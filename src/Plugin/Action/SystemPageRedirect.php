<?php

/**
 * @file
 * Contains Drupal\rules\Plugin\Action\SystemPageRedirect.
 */

namespace Drupal\rules\Plugin\Action;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesActionBase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides "Page redirect" rules action.
 *
 * @Action(
 *   id = "rules_page_redirect",
 *   label = @Translation("Page redirect"),
 *   category = @Translation("System"),
 *   context = {
 *     "url" = @ContextDefinition("uri",
 *       label = @Translation("URL"),
 *       description = @Translation("A Drupal path, path alias, or external URL to redirect to. Enter (optional) queries after ? and (optional) anchor after #."),
 *     ),
 *     "force" = @ContextDefinition("boolean",
 *       label = @Translation("Force redirect"),
 *       description = @Translation("Force the redirect even if another destination parameter is present. Per default Drupal would redirect to the path given as destination parameter, in case it is set. Usually the destination parameter is set by appending it to the URL."),
 *     ),
 *     "destination" = @ContextDefinition("boolean",
 *       label = @Translation("Append destination parameter"),
 *       description = @Translation("Whether to append a destination parameter to the URL, so another redirect issued later on would lead back to the origin page."),
 *     ),
 *   },
 *   provides = {
 *     "redirect" = @ContextDefinition("any",
 *       label = @Translation("Redirect")
 *     ),
 *   }
 * )
 *
 * @todo: Check if we can use context.url.type = "uri".
 * @todo: Check if we can use context.force.restriction = "input".
 * @todo: Check of we can use context.force.optional = true.
 * @todo: Check of we can use context.force.defaultValue = true.
 * @todo: Check if we can use context.destination.restriction = "input".
 * @todo: Check of we can use context.destination.optional = true.
 * @todo: Check of we can use context.destination.defaultValue = true.
 *
 */
class SystemPageRedirect extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The url generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */

  /**
   * Constructs a PageRedirect object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Psr\Log\LoggerInterface $logger
   *   The alias storage service..
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerInterface $logger) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    return $this->t('Page redirect');
  }

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $url = $this->getContextValue('url');
    $force = $this->getContextValue('force');
    $keepDestination = $this->getContextValue('destination');

    // @todo: We need URL to Rules administration pages in order to make sure administrators won't get locked out from them.

    // Make sure we do not redirect during batch processing.
    $batch = batch_get();
    if (isset($batch['current_set'])) {
      $this->logger->log(LogLevel::WARNING, $this->t('Skipped page redirect during batch processing'));
      return;
    }

    // Keep the current destination parameter if there is one set.
    // @todo: This might not work. Test!
    if ($keepDestination) {
      $url .= strpos($url, '?') === FALSE ? '?' : '&';
      $url .= \Drupal::destination()->get();
    }

    // If force is enabled, remove any destination parameter.
    // @todo: This might not work. Test!
    if ($force) {
      \Drupal::destination()->set(null);
    }

    $this->setProvidedValue('redirect', new RedirectResponse($url));
  }

}
