<?php

namespace Drupal\twelve_app\Plugin\Block;

use Drupal\Core\Cache\Cache;

/**
 * Provides a 'Today Progress' block.
 *
 * @Block(
 *   id = "twelve_puzzle",
 *   admin_label = @Translation("Hidden Picture app"),
 *   category = @Translation("Paragraph Blocks")
 * )
 */
class Puzzle extends GameAbstract {

  /**
   * {@inheritdoc}
   */
  public function getGameConfigurationName() {
    return 'twelve_app.puzzle_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function getGameParagraphBundle() {
    return 'puzzle_app';
  }

  /**
   * {@inheritdoc}
   */
  public function getGameExercisesParagraphBundle() {
    return 'puzzle_container';
  }

  public function build() {
    $user = $this->currentUser->getAccount();
    if (empty($user->id())) {
      return [];
    }

    $finished_exercises_nids = $this->getFinishedExercisesNids();
    $progress_nid = 0;
    $progress_node = $this->getUserProgressNode();
    if ($progress_node !== NULL) {
      $progress_nid = $progress_node->id();
    }

    return array_merge(
      parent::build(), [
        '#theme' => 'puzzle',
        '#progress_nid' => $progress_nid,
        '#finished_items' => $finished_exercises_nids,
      ]
    );
  }

  /**
   * @return string[]
   */
  protected function getFinishedExercisesNids() {
    $result = [];

    $progress_node = $this->getUserProgressNode();
    if ($progress_node !== NULL) {
      $finished_exercises = $progress_node
        ->get('field_finished_items')->getValue();

      foreach ($finished_exercises as $item) {
        $result[] = $item['target_id'];
      }
    }

    return $result;
  }

  /**
   * @return string[]
   */
  protected function getUserProgressNode() {
    $nodes = $this->entityTypeManager->getStorage('node')
      ->loadByProperties([
        'uid' => $this->currentUser->getAccount()->id(),
        'field_when' => $this->getCurrentGameNid(),
      ]);

    if (!empty($nodes)) {
      return reset($nodes);
    }

    return NULL;
  }

  protected function prepareExercisesArray() {
    $exercises_array = [];
    $paragraph = $this->findGameExercisesParagraph();
    if (empty($paragraph)) {
      return $exercises_array;
    }

    foreach ($paragraph->field_items as $puzzle_part_reference) {
      /** @var \Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem $puzzle_part_reference */
      $puzzle_part_paragraph = $puzzle_part_reference->entity;
      $exercise_entity = $puzzle_part_paragraph->field_exercise->entity;
      $media_entity = $puzzle_part_paragraph->field_prgf_image->entity;
      /** @var \Drupal\media\Entity\Media $media_entity */
      $media_file_uri = $media_entity->field_media_image->entity->getFileUri();
      $puzzle_image_url = file_create_url($media_file_uri);
      $exercises_array[] = [
        'label' => $exercise_entity->title->value,
        'description' => $exercise_entity->body->value,
        'timer' => $exercise_entity->field_timer->value,
        'gif' => $exercise_entity->field_animation->value,
        'id' => $exercise_entity->id(),
        'puzzle_image_url' => $puzzle_image_url,
      ];
    }

    return $exercises_array;
  }

  public function getCacheTags() {
    $user_id = $this->currentUser->getAccount()->id();

    return Cache::mergeTags(parent::getCacheTags(), [
      'user_id' . $user_id,
      'finished_items' . count($this->getFinishedExercisesNids()),
    ]);
  }

}
