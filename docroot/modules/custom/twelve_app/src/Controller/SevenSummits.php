<?php
namespace Drupal\twelve_app\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\twelve_user\Family;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller
*/
class SevenSummits extends ControllerBase {

  /**
   * @var \Drupal\twelve_user\Family
   */
  protected $family;

  /**
   * @param \Drupal\twelve_user\Family $family
   */
  public function __construct(Family $family) {
    $this->family = $family;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('twelve_user.family')
    );
  }

  /**
   * Render method for /seven-summits/save-hero/{jacketColor}/{fleshTone}
   *
   * @param $jacketColor
   * @param $fleshTone
   *
   * @return AjaxResponse
   */
  public function saveHero($jacketColor, $fleshTone) {
    $this->family->save7SummitsHeroConfig($jacketColor, $fleshTone);
    return new AjaxResponse();
  }

}
