<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\SystemSendEmailTest.
 */

namespace Drupal\Tests\rules\Integration\Action {
  use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;
  use Symfony\Component\EventDispatcher\EventDispatcher;

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
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected $eventDispatcher;

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

      $this->logger = $this->getMock('Psr\Log\LoggerInterface');
      $this->redirectDestination = $this->getMock('Drupal\Core\Routing\RedirectDestinationInterface');
      //$this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
      $this->eventDispatcher = new EventDispatcher();

      $this->container->set('logger.factory', $this->logger);
      $this->container->set('redirect.destination', $this->redirectDestination);
      $this->container->set('event_dispatcher', $this->eventDispatcher);

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
      // @todo: How to test this???
      $this->action->setContextValue('url', 'user')
        ->setContextValue('force', false)
        ->setContextValue('destination', false);

      $this->action->execute();

      $listeners = $this->action->eventDispatcher->getListeners();

      // This is a bad assertion due to fact that I do not know how to access the redirection object
      // within event listener. Help?
      $this->assertArrayHasKey(\Symfony\Component\HttpKernel\KernelEvents::RESPONSE, $listeners);
    }

    /**
     * Tests unsuccessful redirection due to ongoing batch process.
     *
     * @covers ::execute
     */
    public function testRedirectBatch() {
      batch_set('Yaay, batch is running!');
      $this->action->setContextValue('url', 'user')
        ->setContextValue('force', false)
        ->setContextValue('destination', false);

      $this->action->execute();

      $listeners = $this->action->eventDispatcher->getListeners();

      // This is a bad assertion due to fact that I do not know how to access the redirection object
      // within event listener. Help?
      $this->assertArrayNotHasKey(\Symfony\Component\HttpKernel\KernelEvents::RESPONSE, $listeners);
    }
  }
}

namespace {
  if (!function_exists('batch_get')) {

    function batch_set($batch_definition) {
      if ($batch_definition) {
        $batch = &batch_get();
        // Nothing more tan current_set should be mocked for testing purposes.
        $batch['current_set'] = $batch_definition;
      }
    }

    function &batch_get() {
      static $batch = array();
      return $batch;
    }
  }
}
