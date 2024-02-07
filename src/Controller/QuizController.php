<?php

namespace Drupal\quiz_maker\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\quiz_maker\QuizInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Quiz controller class.
 */
class QuizController extends ControllerBase {

  /**
   * Constructs a QuizController object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository.
   */
  public function __construct(
    protected DateFormatterInterface $dateFormatter,
    protected RendererInterface $renderer,
    protected EntityRepositoryInterface $entityRepository
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer'),
      $container->get('entity.repository')
    );
  }

  /**
   * Revision overview.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz.
   *
   * @return array
   *   The render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function revisionOverview(QuizInterface $quiz) {
    $langcode = $quiz->language()->getId();
    $langname = $quiz->language()->getName();
    $languages = $quiz->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    /** @var \Drupal\Core\Entity\RevisionableStorageInterface $quiz_storage */
    $quiz_storage = $this->entityTypeManager()->getStorage('quiz');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', [
      '@langname' => $langname,
      '%title' => $quiz->label()
    ]) : $this->t('Revisions for %title', ['%title' => $quiz->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $rows = [];
    $default_revision = $quiz->getRevisionId();
    $current_revision_displayed = FALSE;

    foreach ($this->getRevisionIds($quiz, $quiz_storage) as $vid) {
      /** @var \Drupal\quiz_maker\QuizInterface $revision */
      $revision = $quiz_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) &&
        $revision->getTranslation($langcode)->isRevisionTranslationAffected()
      ) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision instanceof RevisionLogInterface ? $revision->getRevisionUser() : NULL,
        ];

        // Use revision link to link to revisions that are not active.
        if (!empty($revision->revision_timestamp)) {
          $date = $this->dateFormatter->format($revision->revision_timestamp->value, 'short');
        }
        else {
          $date = $this->dateFormatter->format($revision->getChangedTime(), 'short');
        }

        // We treat also the latest translation-affecting revision as current
        // revision, if it was the default revision, as its values for the
        // current language will be the same of the current default revision in
        // this case.
        $is_current_revision = $vid == $default_revision || (!$current_revision_displayed && $revision->wasDefaultRevision());
        if (!$is_current_revision) {
          $link = Link::fromTextAndUrl($date, new Url('entity.quiz.revision', [
            'quiz' => $quiz->id(),
            'quiz_revision' => $vid
          ]))->toString();
        }
        else {
          $link = $quiz->toLink($date)->toString();
          $current_revision_displayed = TRUE;
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<span class="revision-log">{{ message }}</span>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => !empty($revision->revision_log) ? $revision->revision_log->value : NULL,
                '#allowed_tags' => Xss::getHtmlTagList()
              ],
            ],
          ],
        ];

        $this->renderer->addCacheableDependency($column['data'], $username);
        $row[] = $column;

        if ($is_current_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];

          $rows[] = [
            'data' => $row,
            'class' => ['revision-current'],
          ];
        }
        else {
          $links = [];
          if ($revision->access('revert revision')) {
            $links['revert'] = [
              'title' => $vid < $quiz->getRevisionId() ? $this->t('Revert') : $this->t('Set as current revision'),
              'url' => $has_translations ?
              Url::fromRoute('quiz.revision_revert_translation_confirm', [
                'quiz' => $quiz->id(),
                'quiz_revision' => $vid,
                'langcode' => $langcode
              ]) :
              Url::fromRoute('quiz.revision_revert_confirm', [
                'quiz' => $quiz->id(),
                'quiz_revision' => $vid
              ]),
            ];
          }

          if ($revision->access('delete revision')) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('quiz.revision_delete_confirm', [
                'quiz' => $quiz->id(),
                'quiz_revision' => $vid
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];

          $rows[] = $row;
        }
      }
    }

    $build['quiz_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#attributes' => [
        'class' => [
          'quiz-revision-table',
        ]
      ],
    ];

    $build['pager'] = ['#type' => 'pager'];

    return $build;
  }

  /**
   * Gets a list of quiz revision IDs for a specific quiz.
   *
   * @param \Drupal\quiz_maker\QuizInterface $quiz
   *   The quiz entity.
   * @param \Drupal\Core\Entity\EntityStorageInterface $quiz_storage
   *   The quiz storage.
   *
   * @return int[]
   *   Quiz revision IDs (in descending order).
   */
  protected function getRevisionIds(QuizInterface $quiz, EntityStorageInterface $quiz_storage) {
    $result = $quiz_storage->getQuery()
      ->accessCheck(TRUE)
      ->allRevisions()
      ->condition($quiz->getEntityType()->getKey('id'), $quiz->id())
      ->sort($quiz->getEntityType()->getKey('revision'), 'DESC')
      ->pager(50)
      ->execute();
    return array_keys($result);
  }

}
