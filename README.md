# node_access_count
## Overview
  The Node Access Count module allows the site to track how many times a node is accessed from both the admin side node view and headless (API / frontend) implementations.
  For reporting and analysis purposes, the stored count can be used to understand content popularity and access patterns.

## Table of contents

  Requirements
  Installation
  Configuration
  How it works
  Use cases
  Maintainers
  
## Requirements
  This module requires no modules outside of Drupal core.
  
## Installation
  Install as you would normally install a contributed Drupal module.

## Configuration
    1.Enable the module at Administration > Extend.
    2.Navigate to Administration > Configuration > Content Authoring > Node Access Count.
    3.Select the Content Types for which access tracking should be enabled.
    4.Choose the tracking mode:
        Admin Side Node View – Tracks node views from the Drupal admin interface.
        Headless Implementation – Tracks node access via APIs or headless frontends.
        Both – Tracks access from both
  ### Headless Configuration
    5.An additional Headless Configuration tab will be displayed.
    6.In this tab, configure the API route details used for headless access.
       API route / endpoint path
    7.Save the configuration to enable headless access tracking for the selected content types.
Once configured, access counts will be recorded based on the selected tracking options.

## How it works
  1.Every time a node is accessed, the module increments the access count for that node.
  2.Both admin-side views and headless/API requests are tracked.
  3.The access count is stored in the backend database.
  4.Each node maintains a single cumulative access count.

## Use cases
  1.Track how frequently a node is accessed
  2.Analyze content usage across admin and headless applications
  3.Identify popular or frequently consumed content

