<?php

namespace Drupal\dgi_image_discovery;

use Drupal\Islandora\IslandoraUtils;

/**
 * Utility functions/values.
 */
class Utility {
  const THUMBNAIL_URI = 'http://pcdm.org/use#ThumbnailImage';
  const ORIGINAL_FILE_URI = 'http://pcdm.org/use#OriginalFile';
  const MEDIA_USE_URI_FIELD = IslandoraUtils::MEDIA_USAGE_FIELD .
  '.entity:taxonomy_term.' .
  IslandoraUtils::EXTERNAL_URI_FIELD .
  '.uri';

}
