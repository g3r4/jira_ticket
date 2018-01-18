<?php

namespace Drupal\jira_ticket\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

use JiraRestApi\Issue\IssueService;
use JiraRestApi\JiraException;

/**
 * Defines a form that configures forms module settings.
 */
class JiraTicketConfigurationForm extends ConfigFormBase {

  use JiraTicketFormTrait;

  protected $projects_meta  = [];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'jira_ticket_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'jira_ticket.settings',
    ];
  }

  /**
   *
   */
  protected function getMeta() {

    try {
      $issueService = new IssueService();

      $meta = $issueService->getCreateMeta();

    } catch (JiraException $e) {
      print("Error Occured! " . $e->getMessage());
    }

    return $meta;

  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {

    $config = $this->config('jira_ticket.settings');
    $meta = $this->getMeta();

    $projects = [];

    $this->projects_meta  = [];

    //dump($meta);

    foreach ($meta->projects as $project) {
      $projects[$project->key] = $project->name;
      $issue_types_names = [];
      $issue_types_descriptions = [];
      $issue_type_fields = [];
      foreach ($project->issuetypes as $issue_type) {
        $issue_types_names[$issue_type->name] = $issue_type->name;
        $issue_types_descriptions[$issue_type->name] = $issue_type->description;
        $issue_type_fields[$issue_type->name] = $issue_type->fields;
        $fields_names = [];
        $fields_descriptions = [];
        $fields_required = [];
        $fields_type = [];
        $fields_allowed_values = [];
        foreach ($issue_type->fields as $field) {
          $fields_names[$field->name] = $field->name;
          $allowedValues = [];
          foreach ($field->allowedValues as $allowedValue){
            $allowedValues[] = $allowedValue->name;
          }
          $fields_descriptions[$field->name] = ($field->required? "Field required, ":"Field not required, ") .
                                                "type:" . $field->schema->type .
                                                (empty($allowedValues)? "" : ", allowed values: " . implode("-",$allowedValues));
          $fields_required[$field->name] = $field->required;
          $fields_type[$field->name] = $field->schema->type;
          $fields_allowed_values[$field->name] = $allowedValues;
        }
        $issue_type_fields_names[$issue_type->name] = $fields_names;
        $issue_type_fields_descriptions[$issue_type->name] = $fields_descriptions;
        $issue_type_fields_required[$issue_type->name] = $fields_required;
        $issue_type_fields_type[$issue_type->name] = $fields_type;
        $issue_type_fields_allowed_values[$issue_type->name] = $fields_allowed_values;

      }
      $this->projects_meta[$project->key] = ["issue_types" => $issue_types_names, "issue_type_descriptions" => $issue_types_descriptions, "issue_type_fields" => $issue_type_fields,
                                              "issue_type_fields_names" => $issue_type_fields_names, "issue_type_fields_descriptions" => $issue_type_fields_descriptions,
                                              "issue_type_fields_required" => $issue_type_fields_required, "issue_type_fields_type" => $issue_type_fields_type,
                                              "issue_type_fields_allowed_values" => $issue_type_fields_allowed_values];
    }


    $form['description'] = [
      '#type' => 'item',
      '#markup' => $this->t('Define the fields you want to display on the user\'s exposed form'),
    ];




/*
    if ($form_state->getValue('jira_project') == null && $form['jira_project']['#default_value'] != null) {
        $project = $form['jira_project']['#default_value'];
    } else {
      $project = $form_state->getValue('jira_project');
    }*/




    // Add a color element to the color_wrapper container using the value
    // from temperature to determine which colors to include in the select
    // element.
/*    if ($form['jira_project']['#default_value'] == $form_state->getValue('jira_project')){
      $project = $config->get('jira_project');
    } else if ($form_state->getValue('jira_project') == null) {
      $project = null;
    } else {
      $project = $form_state->getValue('jira_project');
    }*/

    //$project = (!empty($form_state->getValue('jira_project'))) ? $form_state->getValue('jira_project') : $config->get('jira_project');






    $form['tabs'] = [
      '#type' => 'vertical_tabs',
      //'#default_tab' => 'edit-publication',
    ];

    $form['current_config'] = [
      '#type' => 'details',
      '#title' => 'Current saved options',
      '#group' => 'tabs',
    ];

    $form['current_config']['project_table_label'] = [
      '#type' => 'item',
      '#markup' => $this->t("<h2> Jira Project </h2>")
    ];

    $form['current_config']['project_table'] = [
      '#type' => 'table',
      '#header' => [
        'project_key' => t('Key'),
        'project_name' => t('Name'),
      ],
      '#rows' => [
        1 => ['project_key' => $config->get('jira_project'), 'project_name' => $projects[$config->get('jira_project')]]
      ],
      '#empty' => t('No project found'),
    ];

    $form['current_config']['issue_table_label'] = [
      '#type' => 'item',
      '#markup' => $this->t("<h2> Issue  </h2>")
    ];

    $form['current_config']['issue_type_table'] = [
      '#type' => 'table',
      '#header' => [
        'issue_type' => t('Type'),
        'issue_description' => t('Description'),
      ],
      '#rows' => [
        1 => ['issue_type' => $config->get('issue_type'), 'description' => $this->projects_meta[$config->get('jira_project')]['issue_type_descriptions'][$config->get('issue_type')]]
      ],
      '#empty' => t('No Issue type found'),
    ];

    $form['current_config']['fields_table_label'] = [
      '#type' => 'item',
      '#markup' => $this->t("<h2> Fields  </h2>")
    ];

    $fields = $config->get('fields');
    $fields_rows = [];
    foreach ($fields as $field){
      if ($field !== 0) {
        $fields_rows[] = ['field_name' => $field, 'field_required' => $this->projects_meta[$config->get('jira_project')]['issue_type_fields_descriptions'][$config->get('issue_type')][$field]];
      }
    }

    $form['current_config']['fields_table'] = [
      '#type' => 'table',
      '#header' => [
        'field_name' => t('Field name'),
        'field_required' => t('Required'),
      ],
      '#rows' => $fields_rows,
      '#empty' => t('No Issue type found'),
    ];

    $form['current_form'] = [
      '#type' => 'details',
      '#title' => 'Current form preview',
      '#group' => 'tabs',
    ];

    $form['current_form']['form'] = $this->getJiraTraitForm($config);

    $form['form_builder'] = [
      '#type' => 'details',
      '#title' => t('Form builder'),
      '#group' => 'tabs',
    ];

    // The #ajax attribute used in the temperature input element defines an ajax
    // callback that will invoke the 'updateColor' method on this form object.
    // Whenever the temperature element changes, it will invoke this callback
    // and replace the contents of the 'color_wrapper' container with the
    // results of this method call.
    $form['form_builder']['jira_project'] = [
      '#title' => $this->t('Jira Project'),
      '#type' => 'select',
      '#options' => $projects,
      '#empty_option' => $this->t('- Select a project -'),
      //'#default_value' => $config->get('jira_project'),
      '#ajax' => [
        // Could also use [get_class($this), 'updateColor'].
        'callback' => '::updateIssueTypes',
        'wrapper' => 'issue-types-wrapper',
        'effect' => 'fade'
      ],
    ];

    // Add a wrapper that can be replaced with new HTML by the ajax callback.
    // This is given the ID that was passed to the ajax callback in the '#ajax'
    // element above.
    $form['form_builder']['issue_types_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'issue-types-wrapper'],
    ];

    $project = $form_state->getValue('jira_project');
    if (!empty($project)) {
      $form['form_builder']['issue_types_wrapper']['issue_type'] = [
        '#type' => 'select',
        '#title' => $this->t('Issue type'),
        '#options' => $this->projects_meta[$project]['issue_types'],
        '#empty_option' => $this->t('- Select issue type -'),
        //'#default_value' => $config->get('issue_type'),
        '#ajax' => [
          // Could also use [get_class($this), 'updateColor'].
          'callback' => '::updateFields',
          'wrapper' => 'fields-wrapper',
          'effect' => 'fade'
        ],
      ];
    }

    $form['form_builder']['fields_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'fields-wrapper'],
    ];

    $issue_type = $form_state->getValue('issue_type');
    if (!empty($issue_type)) {
      $form['form_builder']['fields_wrapper']['fields'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Fields'),
        '#options' => $this->projects_meta[$project]['issue_type_fields_names'][$issue_type],
      ];
    }

    $project = $form_state->getValue('jira_project');
    $issue_type = $form_state->getValue('issue_type');
    if (!empty($issue_type) && !empty($project)) {
      foreach ($this->projects_meta[$project]['issue_type_fields_descriptions'][$issue_type] as $field => $description) {
        $form['form_builder']['fields_wrapper']['fields'][$field]['#description'] = $description;
        if ($field == 'Project' || $field == 'Issue Type' || $field == 'Summary'){
          $form['form_builder']['fields_wrapper']['fields'][$field]['#default_value'] = $field;
          $form['form_builder']['fields_wrapper']['fields'][$field]['#disabled'] = $field;
        }
      }
    }

    $form['form_builder']['more_options'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Additional options'),
    );

    $form['form_builder']['more_options']['expose_as_block'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Expose this form as a block'),
      '#default_value' => $config->get('expose_as_block')
    );

    $form['form_builder']['more_options']['custom_url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Custom URL'),
      '#default_value' => $config->get('custom_url'),
      '#description' => "This configurable URL redirects the user to the default 
                         ticket creation page path",
    );

    $form['form_builder']['actions']['#type'] = 'actions';
    $form['form_builder']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
      '#button_type' => 'primary',
    ];

    // By default, render the form using system-config-form.html.twig.
    $form['#theme'] = 'system_config_form';


    $form['meta'] = [
      '#type' => 'hidden',
      '#value' => serialize($this->projects_meta)
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    // INIT
    $values = $form_state->getValues();

    // Validate
    if ( $values['jira_project']  == '' ) {
      $form_state->setErrorByName('jira_project', t('Please choose a Jira Project'));
    }

    if ( $values['issue_type']  == '' ) {
      $form_state->setErrorByName('issue_type', t('Please choose an Issue Type'));
    }
/*
    if ( $values['field_test_3']  == '' ) {
      $form_state->setErrorByName('field_test_3', t('Error 3'));
    }*/

    // If validation errors, save them to the hidden form field in JSON format
    if ( $errors = $form_state->getErrors() ) {
      $form['my_module_error_msgs']['#value'] = json_encode($errors);
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();

    $this->config('jira_ticket.settings')
      ->set('meta', $values['meta'])
      ->save();

    $this->config('jira_ticket.settings')
      ->set('jira_project', $values['jira_project'])
      ->save();

    $this->config('jira_ticket.settings')
      ->set('issue_type', $values['issue_type'])
      ->save();

    $this->config('jira_ticket.settings')
      ->set('fields', $values['fields'])
      ->save();

    $this->config('jira_ticket.settings')
      ->set('expose_as_block', $values['expose_as_block'])
      ->save();

    $this->config('jira_ticket.settings')
      ->set('custom_url', $values['custom_url'])
      ->save();

  }

  /**
   * Ajax callback for the color dropdown.
   */
  public function updateIssueTypes(array &$form, FormStateInterface &$form_state) {
    return $form['form_builder']['issue_types_wrapper'];
  }

  public function updateFields(array &$form, FormStateInterface &$form_state) {
    return $form['form_builder']['fields_wrapper'];
  }


}