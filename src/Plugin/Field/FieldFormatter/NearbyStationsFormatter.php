<?php

namespace Drupal\nearby_transport_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\geofield\GeoPHP\GeoPHPInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zigazou\TransportNearby\TransportNearby;

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
   * NearbyStationsFormatter constructor.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\geofield\GeoPHP\GeoPHPInterface $geophp_wrapper
   *   The geoPhpWrapper.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    GeoPHPInterface $geophp_wrapper,
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->geoPhpWrapper = $geophp_wrapper;
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
      $container->get('geofield.geophp')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

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
      'max_distance' => 500,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $maxDistance = $this->getSetting('max_distance');
    $stations = new TransportNearby('../data/transport.db');

    foreach ($items as $delta => $item) {
      $output = ['#markup' => ''];
      $geom = $this->geoPhpWrapper->load($item->value);
      if ($geom) {
        // If the geometry is not a point, get the centroid.
        if ($geom->getGeomType() !== 'Point') {
          $geom = $geom->centroid();
        }
        /** @var \Point $geom */
        $stops = $stations->prettyFindStations(
          $geom->y(),
          $geom->x(),
          $maxDistance
        );

        $nearPoints = [];
        foreach ($stops as $name => $stopsItem) {
          $nearPoints[$name] = [
            '#theme' => 'nearby_routes',
            '#routes' => $stopsItem['points'],
          ];
        }

        $output = [
          '#theme' => 'nearby_stations',
          '#lat' => $geom->y(),
          '#lon' => $geom->x(),
          '#near_points' => $nearPoints,
          '#max_distance' => $maxDistance,
        ];
      }
      $elements[$delta] = $output;
    }

    return $elements;
  }

}
