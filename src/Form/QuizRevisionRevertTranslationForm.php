<?php

namespace Drupal\quiz_maker\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\entity\Form\RevisionRevertForm;
use Drupal\quiz_maker\QuizInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for reverting a quiz revision for a single translation.
 *
 * @internal
 */
class QuizRevisionRevertTranslationForm extends RevisionRevertForm {

  /**
   * The langcode.
   *
   * @var mixed|null
   */
  private mixed $langcode;

  /**
   * Constructs a new NodeRevisionRevertTranslationForm.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $quizStorage
   *   The quiz storage.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundle_information
   *   The bundle info service.
   */
  public function __construct(
    protected EntityStorageInterface $quizStorage,
    protected DateFormatterInterface $date_formatter,
    protected LanguageManagerInterface $languageManager,
    protected TimeInterface $time,
    protected EntityTypeBundleInfoInterface $bundle_information
  ) {
    parent::__construct($date_formatter, $bundle_information);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('quiz'),
      $container->get('date.formatter'),
      $container->get('language_manager'),
      $container->get('datetime.time'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'quiz_revision_revert_translation_confirm';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    /** @var \Drupal\Core\Entity\RevisionLogInterface $revision */
    $revision = $this->revision;
    return $this->t(
      'Are you sure you want to revert @language translation to the revision from %revision-date?',
      [
        '@language' => $this->languageManager->getLanguageName($this->revision->language()->getId()),
        '%revision-date' => $this->dateFormatter->format($revision->getRevisionCreationTime())
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $quiz_revision = NULL, $langcode = NULL) {
    $this->langcode = $langcode;
    $form = parent::buildForm($form, $form_state, $quiz_revision);
    /** @var \Drupal\Core\Entity\TranslatableRevisionableInterface $revision */
    $revision = $this->revision;

    // Unless untranslatable fields are configured to affect only the default
    // translation, we need to ask the user whether they should be included in
    // the revert process.
    $default_translation_affected = $revision->isDefaultTranslationAffectedOnly();
    $form['revert_untranslated_fields'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Revert content shared among translations'),
      '#default_value' => $default_translation_affected && $revision->getTranslation($langcode)->isDefaultTranslation(),
      '#access' => !$default_translation_affected,
    ];

    return $form;
  }

  /**
   * Prepare reverted revision.
   *
   * @param \Drupal\quiz_maker\QuizInterface $revision
   *   The revision.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Entity\RevisionableInterface
   *   The entity revision.
   */
  protected function prepareRevertedRevision(QuizInterface $revision, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
    $storage = $this->quizStorage;
    $translation = $revision->getTranslation($this->langcode);
    return $storage->createRevision($translation, TRUE);
  }

}
