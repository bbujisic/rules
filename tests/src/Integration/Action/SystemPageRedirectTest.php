<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\SystemSendEmailTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;

/**
 * @coversDefaultClass \Drupal\rules\Plugin\Action\SystemPageRedirect
 * @group rules_actions
 *
 * @todo: Write test for redirection to rules admin page
 * @todo: Write test for redirection to external link
 * @todo: Write test for redirection with destination
 */
class SystemPageRedirectTest extends RulesIntegrationTestBase {

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Plugin\Action\SystemPageRedirect
   */
  protected $action;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    require_once __DIR__ . '/../../../../../../core/includes/form.inc';

    $this->logger = $this->getMock('Psr\Log\LoggerInterface');
    $this->redirectDestination = $this->getMock('Drupal\Core\Routing\RedirectDestinationInterface');

    $this->container->set('logger.factory', $this->logger);
    $this->container->set('redirect.destination', $this->redirectDestination);

    $this->action = $this->actionManager->createInstance('rules_page_redirect');
  }

  /**
   * Tests the summary.
   *
   * @covers ::summary
   */
  public function testSummary() {
    $this->assertEquals('Page redirect', $this->action->summary());
  }

  /**
   * Tests redirection to internal path.
   *
   * @covers ::execute
   */
  public function testRedirectInternal() {
    $this->action->setContextValue('url', 'user')
      ->setContextValue('force', false)
      ->setContextValue('destination', false);

    $this->action->execute();

    /* @var \Symfony\Component\HttpFoundation\RedirectResponse $redirect */
    $redirect = $this->action->getProvidedContext('redirect')
      ->getContextValue();

    $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $redirect);

    $this->assertEquals($redirect->getTargetUrl(), 'user');
  }
}
