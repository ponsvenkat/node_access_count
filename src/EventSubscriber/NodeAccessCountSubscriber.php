<?php

namespace Drupal\node_access_count\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscribes to node view events to track admin node views.
 */
class NodeAccessCountSubscriber implements EventSubscriberInterface {

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new NodeViewCountSubscriber.
   */
  public function __construct(Connection $database, AccountProxyInterface $current_user, ConfigFactoryInterface $config_factory) {
    $this->database = $database;
    $this->currentUser = $current_user;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Subscribe to kernel request event.
    $events[KernelEvents::REQUEST][] = ['onKernelRequest'];
    return $events;
  }

  /**
   * Tracks admin views of node pages.
   */
  public function onKernelRequest(RequestEvent $event): void {
    // Only handle the main request, not sub-requests.
    if (!$event->isMainRequest()) {
      return;
    }

    $request = $event->getRequest();
    $route_name = $request->attributes->get('_route');
    $path = $request->getPathInfo();
    
    //  Skip admin/config and internal system routes.
    $admin_prefixes = ['/admin', '/core', '/system', '/devel'];
    foreach ($admin_prefixes as $prefix) {
      if (strpos($path, $prefix) === 0) {
        return;
      }
    }

    $api_settings = false;
    // Load configurations.
    $config = $this->configFactory->get('node_access_count.settings');
    $enabled_types = $config->get('tracking') ?? [];
    // Load API configuration.
    $api_config = $this->configFactory->get('node_access_count.api_settings');
    $endpoint_details = $api_config->get('api_endpoints') ?? [];

    if(!empty($endpoint_details)){
      foreach ($endpoint_details as $pattern) {
        // Convert Drupal-style placeholder {node_id} to regex capture group.
        $regex = '#^' . str_replace('/', '\/', preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $pattern)) . '$#';
        if (preg_match($regex, $path, $matches)) {
          if (!empty($matches['node_id'])) {
            $nid = (int) $matches['node_id'];
            $node = Node::load($nid);
            if (!array_key_exists($node->bundle(), $enabled_types)) {
              return;
            }
            else {
              $type_settings = $enabled_types[$node->bundle()];
              if (empty($type_settings['api'])) {
                return;
              }
              else {
                $api_settings = true;
              }
            }
          }
        }
      }
    }

    if(!$api_settings) {
      $node = $request->attributes->get('node');
      
      if (!$node instanceof NodeInterface) {
        return;
      }

      // We only care about canonical node view pages.
      if ($route_name !== 'entity.node.canonical') {
        return;
      }

      // Check if tracking is enabled for this content type.
      if (!array_key_exists($node->bundle(), $enabled_types)) {
        return;
      }
      else {
        $type_settings = $enabled_types[$node->bundle()];
        if (empty($type_settings['admin'])) {
          return;
        }
      }
    }
    // Update both tables
    $this->updateNodeViewCount($node);    
  }

  function updateNodeViewCount(NodeInterface $node): void {
    $uid = $this->currentUser->id();
    $nid = $node->id();
    $timestamp = \Drupal::time()->getRequestTime();
    
    $user_existing = $this->database->select('node_access_count', 'nac')
        ->fields('nac', ['nid'])
        ->condition('nid', $nid)
        ->condition('uid', $uid)
        ->execute()
        ->fetchField();

    if (!$user_existing) {
        // Create new record for user access.
        $this->database->insert('node_access_count')
            ->fields([
            'nid' => $nid,
            'uid' => $uid,
            'timestamp' => $timestamp,
            ])
        ->execute();

        // Update or insert into node_access_summary table.
        $this->database->merge('node_access_summary')
          ->key('nid',$nid)
          ->fields(['last_updated' => $timestamp])
          ->expression('total_views', 'total_views + 1')
          ->execute();
    }
    return;    
  }
}
