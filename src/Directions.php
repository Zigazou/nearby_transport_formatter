<?php

namespace Drupal\nearby_transport_formatter;

/**
 * @file
 * Directions constants.
 */

/**
 * Directions constants and human-readable labels.
 */
class Directions {
  /**
   * No direction.
   *
   * @var int
   */
  public const DIRECTION_NONE = 0b0000;

  /**
   * North direction.
   *
   * @var int
   */
  public const DIRECTION_NORTH = 0b0001;

  /**
   * Northeast direction.
   *
   * @var int
   */
  public const DIRECTION_NORTHEAST = 0b0101;

  /**
   * East direction.
   *
   * @var int
   */
  public const DIRECTION_EAST = 0b0100;

  /**
   * Southeast direction.
   *
   * @var int
   */
  public const DIRECTION_SOUTHEAST = 0b0111;

  /**
   * South direction.
   *
   * @var int
   */
  public const DIRECTION_SOUTH = 0b0011;

  /**
   * Southwest direction.
   *
   * @var int
   */
  public const DIRECTION_SOUTHWEST = 0b1111;

  /**
   * West direction.
   *
   * @var int
   */
  public const DIRECTION_WEST = 0b1100;

  /**
   * Northwest direction.
   *
   * @var int
   */
  public const DIRECTION_NORTHWEST = 0b1101;

  /**
   * Convert a direction constant to a human-readable string.
   *
   * @param int $direction
   *   The direction constant.
   *
   * @return string
   *   The human-readable direction.
   */
  public static function toHuman(int $direction): string {
    switch ($direction) {
      case self::DIRECTION_NONE:
        return '';

      case self::DIRECTION_NORTH:
        return 'au nord';

      case self::DIRECTION_NORTHEAST:
        return 'au nord-est';

      case self::DIRECTION_EAST:
        return 'à l\'est';

      case self::DIRECTION_SOUTHEAST:
        return 'au sud-est';

      case self::DIRECTION_SOUTH:
        return 'au sud';

      case self::DIRECTION_SOUTHWEST:
        return 'au sud-ouest';

      case self::DIRECTION_WEST:
        return 'à l\'ouest';

      case self::DIRECTION_NORTHWEST:
        return 'au nord-ouest';

      default:
        return '';
    }
  }

}
