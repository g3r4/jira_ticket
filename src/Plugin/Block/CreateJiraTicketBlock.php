<?php

namespace Drupal\jira_ticket\Plugin\Block;

use Drupal\jira_ticket\Form\CreateJiraTicketForm;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\jira_ticket\Form\JiraTicketFormTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormBuilderInterface;

/**
 * Provides a 'CreateJiraTicketBlock' block.
 *
 * @Block(
 *  id = "create_jira_ticket_block",
 *  admin_label = @Translation("Create jira ticket block"),
 * )
 */
class CreateJiraTicketBlock extends BlockBase implements ContainerFactoryPluginInterface {
  use JiraTicketFormTrait;

  protected $config;
  protected $form_builder;

  /**
   * @inheritdoc
   */
  public function __construct(ConfigFactoryInterface $configFactory, FormBuilderInterface $formBuilder) {
    $this->config = $configFactory;
    $this->form_builder = $formBuilder;
  }

  /**
   * @inheritdoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('config.factory'),
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $config = $this->config->get('jira_ticket.settings');

    if ($config->get('expose_as_block')) {
      $form = $this->form_builder->getForm(CreateJiraTicketForm::class);
      return $form;
    }
    else {
      $build = [];
      $build['create_jira_ticket_block']['#markup'] = 'Please, enable the option 
                                                       to expose this form as a 
                                                       block in the configuration 
                                                       options';
      return $build;
    }

  }

}
