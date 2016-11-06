<?php

namespace Drupal\categorized\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides a 'Categorized' block.
 *
 * @Block(
 *   id = "categorized",
 *   admin_label = @Translation("Categorized")
 * )
 */
class CategorizedBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();

    // Add a form field to the existing block configuration form.
    $form['description'] = array(
      '#type'          => 'text_format',
      '#title'         => $this->t('Description'),
      '#description'   => $this->t('Write a description of the block.'),
      '#default_value' => isset($config['description']['value']) ? $config['description']['value'] : '',
      '#editor'        => TRUE,
    );
    $form['categories']    = array(
      '#title' => $this->t('Categories'),
      '#description' => $this->t('Select some categories.'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'taxonomy_vocabulary',
      '#value' => isset($config['categories']) ? $config['categories'] : NULL
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['description'] = $form_state->getValue('description');
    $this->configuration['categories']    = $form_state->getValue('categories');
  }

  /**
   * Get all terms from vocabulary.
   *
   * @param integer $vid
   *
   * @return array
   */
  public function getTerms($vid) {
    $query = \Drupal::entityQuery('taxonomy_term');
    $query->condition('vid', $vid);
    $tids = $query->execute();
    $terms = array();
    foreach ($tids as $key => $value) {
      $term_name = \Drupal\taxonomy\Entity\Term::load($value)->get('name')->value;
      $terms[$key] = ['tid' => $value, 'name' => $term_name];
    }
    return $terms;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $description = isset($config['description']['value']) ? $config['description']['value'] : '';
    $categories    = isset($config['categories']) ? $config['categories'] : '';

    if ( isset($config['categories'])) {
     $terms =  $this->getTerms($config['categories']);
    }
    else {
      $terms = null;
    }
    return array(
        '#theme' => 'categorized_block',
        '#description' => $description,
        '#categories' => $categories,
        '#terms' => $terms
      );
  }
}
