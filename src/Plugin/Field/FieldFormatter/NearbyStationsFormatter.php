<?php

namespace Drupal\nearby_transport_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\geofield\GeoPHP\GeoPHPInterface;
use Drupal\leaflet\LeafletService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zigazou\TransportNearby\TransportNearby;

use Drupal\nearby_transport_formatter\Categories;
use Drupal\nearby_transport_formatter\Directions;

/**
 * Plugin implementation of the 'nearby_stations' formatter.
 *
 * @FieldFormatter(
 *   id = "nearby_stations",
 *   label = @Translation("Nearby stations"),
 *   field_types = {
 *     "geofield"
 *   }
 * )
 */
final class NearbyStationsFormatter extends FormatterBase implements ContainerFactoryPluginInterface {
  /**
   * The geoPhpWrapper service.
   *
   * @var \Drupal\geofield\GeoPHP\GeoPHPInterface
   */
  protected $geoPhpWrapper;

  /**
   * The transportNearby object.
   *
   * @var \Zigazou\TransportNearby\TransportNearby
   */
  protected $transportNearby;

  /**
   * The Leaflet service.
   *
   * @var \Drupal\leaflet\LeafletService
   */
  protected $leafletService;

  /**
   * The extension path resolver.
   *
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected $extensionPathResolver;

  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    GeoPHPInterface $geophp_wrapper,
    TransportNearby $transport_nearby,
    LeafletService $leaflet_service,
    ExtensionPathResolver $extension_path_resolver,
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->geoPhpWrapper = $geophp_wrapper;
    $this->transportNearby = $transport_nearby;
    $this->leafletService = $leaflet_service;
    $this->extensionPathResolver = $extension_path_resolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('geofield.geophp'),
      new TransportNearby('../data/transport.db'),
      $container->get('leaflet.service'),
      $container->get('extension.path.resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $leafletMaps = $this->leafletService->leafletMapGetInfo();
    $leafletMapOptions = [];
    foreach ($leafletMaps as $mapId => $mapDefinition) {
      $leafletMapOptions[$mapId] = $mapDefinition['label'] ?? $mapId;
    }

    $form['leaflet_map'] = [
      '#title' => $this->t('Leaflet map background'),
      '#type' => 'select',
      '#options' => $leafletMapOptions,
      '#default_value' => $this->getSetting('leaflet_map'),
      '#empty_option' => $this->t('- Select a map -'),
      '#description' => $this->t('Choose one of the Leaflet maps defined in Drupal.'),
    ];

    $form['max_distance'] = [
      '#title' => $this->t('Maximum distance in meters'),
      '#type' => 'number',
      '#min' => 0,
      '#max' => 1000,
      '#step' => 50,
      '#default_value' => $this->getSetting('max_distance'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'leaflet_map' => '',
      'max_distance' => 500,
    ] + parent::defaultSettings();
  }

  /**
   * Sort the routes by their name.
   *
   * @param array $routes
   *   The routes to sort.
   *
   * @return array
   *   The sorted routes.
   */
  protected function sortRoutesByName($routes) {
    usort($routes, function ($a, $b) {
      return strcmp($a['name'], $b['name']);
    });

    return $routes;
  }

  /**
   * Prepare the metros for the template.
   */
  protected function prepareMetros($stops) {
    $metros = [];
    foreach ($stops as $category => $stopsItem) {
      if ($category === Categories::CATEGORY_METRO) {
        foreach ($stopsItem as $station => $metro) {
          $metros[$station] = [
            '#theme' => 'nearby_routes',
            '#routes' => $metro['points'],
          ];
        }
      }
    }

    if (empty($metros)) {
      return [];
    }

    return $metros;
  }

  /**
   * Prepare the trains for the template.
   */
  protected function prepareTrains($stops) {
    $trains = [];
    foreach ($stops as $category => $stopsItem) {
      if ($category === Categories::CATEGORY_TRAIN) {
        foreach ($stopsItem as $station => $train) {
          $trains[$station] = [
            '#theme' => 'nearby_routes',
            '#routes' => $this->sortRoutesByName($train['points']),
          ];
        }
      }
    }

    if (empty($trains)) {
      return [];
    }

    return $trains;
  }

  /**
   * Prepare the long buses for the template.
   */
  protected function prepareBusLongs($stops) {
    $busLongs = [];
    foreach ($stops as $category => $stopsItem) {
      if ($category === Categories::CATEGORY_BUS_LONG) {
        foreach ($stopsItem as $station => $busLong) {
          $busLongs[$station] = [
            '#theme' => 'nearby_routes',
            '#routes' => $this->sortRoutesByName($busLong['points']),
          ];
        }
      }
    }

    if (empty($busLongs)) {
      return [];
    }

    return $busLongs;
  }

  /**
   * Prepare the short buses for the template.
   */
  protected function prepareBusShorts($stops) {
    $busShorts = [];
    foreach ($stops as $category => $stopsItem) {
      if ($category === Categories::CATEGORY_BUS_SHORT) {
        foreach ($stopsItem as $station => $busShort) {
          $busShorts[$station] = [
            '#theme' => 'nearby_routes',
            '#routes' => $this->sortRoutesByName($busShort['points']),
          ];
        }
      }
    }

    if (empty($busShorts)) {
      return [];
    }

    return $busShorts;
  }

  /**
   * Prepare the ferries for the template.
   */
  protected function prepareFerries($stops) {
    $ferries = [];
    foreach ($stops as $category => $stopsItem) {
      if ($category === Categories::CATEGORY_FERRY) {
        foreach ($stopsItem as $station => $ferry) {
          $ferries[$station] = [
            '#theme' => 'nearby_routes',
            '#routes' => $this->sortRoutesByName($ferry['points']),
          ];
        }
      }
    }

    if (empty($ferries)) {
      return [];
    }

    return $ferries;
  }

  /**
   * Prepare the lovelos for the template.
   */
  protected function prepareLovelos($cycleStops) {
    $lovelos = [];
    foreach ($cycleStops as $category => $stopsItem) {
      if ($category === Categories::CATEGORY_LOVELO) {
        foreach ($stopsItem as $station => $lovelo) {
          $lovelos[] = [
            '#theme' => 'nearby_cycle',
            '#station_name' => $station,
            '#free' => $lovelo['points'][0]['free'],
            '#distance' => $lovelo['points'][0]['distance'],
            '#direction' => $lovelo['points'][0]['direction'],
            '#lat' => $lovelo['points'][0]['lat'],
            '#lon' => $lovelo['points'][0]['lon'],
            '#capacity' => $lovelo['points'][0]['capacity'],
          ];
        }
      }
    }

    if (empty($lovelos)) {
      return [];
    }

    return $lovelos;
  }

  /**
   * Prepare the bike locks for the template.
   */
  protected function prepareBikeLocks($cycleStops) {
    static $bikeLockCategories = [
      Categories::CATEGORY_BIKE_HIGH_RACK,
      Categories::CATEGORY_BIKE_PARK,
      Categories::CATEGORY_BIKE_BOLLARD,
      Categories::CATEGORY_BIKE_LOW_RACK,
      Categories::CATEGORY_BIKE_HANDLEBAR,
      Categories::CATEGORY_BIKE_DOUBLE_DECK,
    ];

    if (empty($cycleStops)) {
      return [];
    }

    $bikeLocks = [];
    $maxDistance = ((integer) $this->getSetting('max_distance')) / 5;
    foreach ($cycleStops as $category => $stopsItem) {
      if (!in_array($category, $bikeLockCategories, TRUE)) {
        continue;
      }

      foreach ($stopsItem as $station => $bikeLock) {
        if (empty($bikeLock['points'])) {
          continue;
        }

        $points = [];
        $category_name = Categories::CATEGORIES[$category][1];
        foreach ($bikeLock['points'] as $point) {
          if ($point['distance'] > $maxDistance) {
            continue;
          }

          $points[] = [
            '#theme' => 'nearby_lock',
            '#category_name' => $category_name,
            '#distance' => $point['distance'],
            '#direction' => Directions::toHuman($point['direction']),
            '#lat' => $point['lat'],
            '#lon' => $point['lon'],
            '#capacity' => $point['capacity'],
          ];
        }

        if (empty($points)) {
          continue;
        }

        usort($points, function ($a, $b) {
          return $a['#distance'] <=> $b['#distance'];
        });

        $bikeLocks[$category] = [
          '#theme' => 'nearby_locks',
          '#locks' => $points,
        ];
      }
    }

    if (empty($bikeLocks)) {
      return [];
    }

    return $bikeLocks;
  }

  /**
   * Generate a leaflet map.
   *
   * @param float $lat
   *   The latitude of the point of interest.
   * @param float $lon
   *   The longitude of the point of interest.
   * @param array $stops
   *   The stops to display on the map.
   * @param array $cycleStops
   *   The cycle stops to display on the map.
   *
   * @return array
   *   The render array for the map.
   */
  protected function generateMap($lat, $lon, $stops, $cycleStops) {
    $base_url = '/' . $this->extensionPathResolver->getPath(
      'module',
      'nearby_transport_formatter'
    );

    $features = [];

    // Add the point of interest as a feature on the map.
    $features[] = [
      'type' => 'point',
      'lat' => $lat,
      'lon' => $lon,
      'category' => "Centre d’intérêt",
      'name' => "Centre d’intérêt",
      'icon' => [
        'iconUrl' => $base_url . "/icons/people.svg",
        'iconSize' => ['x' => 32, 'y' => 36],
        'iconAnchor' => ['x' => 16, 'y' => 36],
      ],
    ];

    // Add the stops as features on the map.
    foreach ($stops as $category => $stopsItem) {
      $icon_url = $base_url . Categories::CATEGORY_ICONS[$category];
      foreach ($stopsItem as $station => $stop) {
        $description = mb_ucfirst(Categories::CATEGORIES[$category][0])
                     . ' '
                     . $station;

        $precisions = [];
        foreach ($stop['points'] as $point) {
          if ($category === Categories::CATEGORY_METRO) {
            $precisions[] = $point['long_name'];
          }
          else {
            $precisions[] = $point['name'];
          }
        }

        $description .= ' (' . implode(', ', $precisions) . ')';

        $features[] = [
          'type' => 'point',
          'lat' => $stop['points'][0]['lat'],
          'lon' => $stop['points'][0]['lon'],
          'category' => Categories::CATEGORIES[$category][1],
          'name' => $station,
          'popup' => ['value' => $description],
          'icon' => [
            'iconUrl' => $icon_url,
            'iconSize' => ['x' => 32, 'y' => 36],
            'iconAnchor' => ['x' => 16, 'y' => 36],
            'popupAnchor' => ['x' => 16, 'y' => 0],
          ],
        ];
      }
    }

    // Add the cycle stops as features on the map.
    $maxDistance = ((integer) $this->getSetting('max_distance')) / 5;
    foreach ($cycleStops as $category => $stopsItem) {
      $icon_url = $base_url . Categories::CATEGORY_ICONS[$category];
      foreach ($stopsItem as $station => $stop) {
        foreach ($stop['points'] as $point) {
          // Do not add bike locks that are too far from the point of interest.
          if ($category !== Categories::CATEGORY_LOVELO) {
            if ($point['distance'] > $maxDistance) {
              continue;
            }

            $station = mb_ucfirst(Categories::CATEGORIES[$category][1])
                     . ' (' . $point['capacity'] . ' places)';
          }
          else {
            $station = "Station Lovélo $station";
          }

          $features[] = [
            'type' => 'point',
            'lat' => $point['lat'],
            'lon' => $point['lon'],
            'category' => Categories::CATEGORIES[$category][1],
            'name' => $station,
            'popup' => ['value' => $station],
            'icon' => [
              'iconUrl' => $icon_url,
              'iconSize' => ['x' => 32, 'y' => 36],
              'iconAnchor' => ['x' => 16, 'y' => 36],
              'popupAnchor' => ['x' => 16, 'y' => 0],
            ],
          ];
        }
      }
    }

    $selectedMapId = $this->getSetting('leaflet_map');
    $map = !empty($selectedMapId) ? $this->leafletService->leafletMapGetInfo($selectedMapId) : [];

    if (empty($map)) {
      // Fallback map if no Leaflet map is selected in formatter settings.
      $map = [
        'label' => 'Transports',
        'description' => 'Transports à proximité',
        'settings' => [
          'zoomControlPosition' => 'topright',
          'map_lazy_load' => ['lazy_load' => FALSE],
        ],
        'layers' => [
          'OpenStreetMap' => [
            'urlTemplate' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            'options' => [
              'attribution' => '&copy; OpenStreetMap contributors',
              'maxZoom' => 19,
            ],
          ],
        ],
      ];
    }

    $map['id'] = 'nearby_transports_map';
    $map['settings']['zoomControlPosition'] =
      $map['settings']['zoomControlPosition'] ?? 'topright';
    $map['settings']['map_lazy_load'] =
      $map['settings']['map_lazy_load'] ?? ['lazy_load' => FALSE];
    $map['settings']['popup'] = TRUE;

    return \Drupal::service('leaflet.service')
      ->leafletRenderMap($map, $features, '100%');
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $maxDistance = $this->getSetting('max_distance');
    $stations = $this->transportNearby;

    foreach ($items as $delta => $item) {
      $output = ['#markup' => ''];
      $geom = $this->geoPhpWrapper->load($item->value);
      if ($geom) {
        // If the geometry is not a point, get the centroid.
        if ($geom->getGeomType() !== 'Point') {
          $geom = $geom->centroid();
        }

        /** @var \Point $geom */
        $x = $geom->x();
        $y = $geom->y();
        $stops = $stations->prettyFindStations($y, $x, $maxDistance);
        $cycleStops = $stations->prettyFindCycleStops($y, $x, $maxDistance);

        $output = [
          '#theme' => 'nearby_stations',
          '#lat' => $y,
          '#lon' => $x,
          '#metros' => self::prepareMetros($stops),
          '#trains' => self::prepareTrains($stops),
          '#bus_longs' => self::prepareBusLongs($stops),
          '#bus_shorts' => self::prepareBusShorts($stops),
          '#ferries' => self::prepareFerries($stops),
          '#lovelos' => self::prepareLovelos($cycleStops),
          '#bike_locks' => self::prepareBikeLocks($cycleStops),
          '#max_distance' => $maxDistance,
          '#map' => $this->generateMap($y, $x, $stops, $cycleStops),
        ];
      }
      $elements[$delta] = $output;
    }

    return $elements;
  }

}
