<?php

namespace Drupal\nearby_transport_formatter;

/**
 * Categories of nearby facilities.
 *
 * @package Drupal\nearby_transport_formatter
 */
class Categories {
  public const CATEGORY_OTHER = 0;
  public const CATEGORY_METRO = 1;
  public const CATEGORY_TRAIN = 2;
  public const CATEGORY_BUS_LONG = 3;
  public const CATEGORY_BUS_SHORT = 4;
  public const CATEGORY_FERRY = 5;
  public const CATEGORY_LOVELO = 6;
  public const CATEGORY_BIKE_HIGH_RACK = 7;
  public const CATEGORY_BIKE_PARK = 8;
  public const CATEGORY_BIKE_BOLLARD = 9;
  public const CATEGORY_BIKE_LOW_RACK = 10;
  public const CATEGORY_BIKE_HANDLEBAR = 11;
  public const CATEGORY_BIKE_DOUBLE_DECK = 12;

  /***
   * The categories of nearby facilities.
   *
   * @var array
   */
  public const CATEGORIES = [
    self::CATEGORY_OTHER  => ['autre', 'autres'],
    self::CATEGORY_METRO  => ['métro', 'métros'],
    self::CATEGORY_TRAIN  => ['train', 'trains'],
    self::CATEGORY_BUS_LONG  => ['car', 'cars'],
    self::CATEGORY_BUS_SHORT  => ['bus', 'bus'],
    self::CATEGORY_FERRY  => ['navette fluviale', 'navettes fluviales'],
    self::CATEGORY_LOVELO  => ['Lovélo', 'Lovélos'],
    self::CATEGORY_BIKE_HIGH_RACK  => ['arceau vélo', 'arceaux vélo'],
    self::CATEGORY_BIKE_PARK  => ['parc à vélo', 'parcs à vélo'],
    self::CATEGORY_BIKE_BOLLARD  => ['potelet', 'potelets'],
    self::CATEGORY_BIKE_LOW_RACK  => ['ratelier', 'rateliers'],
    self::CATEGORY_BIKE_HANDLEBAR  => ['support guidon', 'supports guidon'],
    self::CATEGORY_BIKE_DOUBLE_DECK  => ['rack double étage', 'racks double étage'],
  ];

  /**
   * The icons for each category.
   *
   * @var array
   */
  public const CATEGORY_ICONS = [
    self::CATEGORY_OTHER => '',
    self::CATEGORY_METRO => '/icons/metro.svg',
    self::CATEGORY_TRAIN => '/icons/train.svg',
    self::CATEGORY_BUS_LONG => '/icons/bus.svg',
    self::CATEGORY_BUS_SHORT => '/icons/bus.svg',
    self::CATEGORY_FERRY => '/icons/boat.svg',
    self::CATEGORY_LOVELO => '/icons/bike.svg',
    self::CATEGORY_BIKE_HIGH_RACK => '/icons/arceau.svg',
    self::CATEGORY_BIKE_PARK => '/icons/arceau.svg',
    self::CATEGORY_BIKE_BOLLARD => '/icons/arceau.svg',
    self::CATEGORY_BIKE_LOW_RACK => '/icons/arceau.svg',
    self::CATEGORY_BIKE_HANDLEBAR => '/icons/arceau.svg',
    self::CATEGORY_BIKE_DOUBLE_DECK => '/icons/arceau.svg',
  ];
}
