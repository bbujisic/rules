<?php

/**
 * @file
 * Contains \Drupal\Tests\rules\Integration\Action\SystemSendEmailTest.
 */

namespace Drupal\Tests\rules\Integration\Action;

use Drupal\Tests\rules\Integration\RulesIntegrationTestBase;
use Drupal\Core\Language\LanguageInterface;
use Psr\Log\LogLevel;
use Drupal\Component\Utility\SafeMarkup;

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
  protected $redirectDestination;

  /**
   * The action to be tested.
   *
   * @var \Drupal\rules\Plugin\Action\SystemPageRedirect
   */
  protected $action;

//  private function deleteMe() {
//    // Test the page redirect.
//    $node = $this->drupalCreateNode();
//    $rule = rules_reaction_rule();
//    $rule->event('node_view')
//      ->action('redirect', array('url' => 'user'));
//    $rule->save('test2');
//
//    $this->drupalGet('node/' . $node->nid);
//    $this->assertEqual($this->getUrl(), url('user', array('absolute' => TRUE)), 'Redirect has been issued.');
//
//    // Also test using a url including a fragment.
//    $actions = $rule->actions();
//    $actions[0]->settings['url'] = 'user#fragment';
//    $rule->save();
//
//    $this->drupalGet('node/' . $node->nid);
//    $this->assertEqual($this->getUrl(), url('user', array('absolute' => TRUE, 'fragment' => 'fragment')), 'Redirect has been issued.');
//  }


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
    $redirect = $this->action->getProvidedContext('redirect')->getContextValue();

    $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $redirect);

    $this->assertEquals($redirect->getTargetUrl(), 'user');

  }
}
