<?php

namespace Drupal\jira_ticket\Form;

trait JiraTicketFormTrait{

  public function getJiraTraitForm($config){
    /*
     * @todo: Lets make sure we TypeHint $config interface this next iteration
     */
    $jira_ticket_form = array();
    $meta = unserialize($config->get('meta'));

    $saved_project = $config->get('jira_project');
    $saved_issue_type = $config->get('issue_type');
    $saved_fields = $config->get('fields');

    foreach ($meta[$saved_project]["issue_type_fields"][$saved_issue_type] as $field){
      // Do not add Project or Issue type to the final form, since those need
      // to be added anyway and should not be visible to the end user
      if ($saved_fields[$field->name] !== 0 && $saved_fields[$field->name] !==
          'Project' && $saved_fields[$field->name] !== 'Issue Type'){
        // Find if it has allowed values and build the select widget element with
        // those options
        if (count($meta[$saved_project]["issue_type_fields_allowed_values"][$saved_issue_type][$saved_fields[$field->name]]))
        {
          $jira_ticket_form[] = [
            '#title' => $field->name,
            '#type' => 'select',
            '#options' => $meta[$saved_project]["issue_type_fields_allowed_values"][$saved_issue_type][$saved_fields[$field->name]],
            '#empty_option' => $this->t('- Select ' . $field->name . '-'),
          ];
        } else {
          $jira_ticket_form[] = [
            '#title' => $field->name,
            '#type' => 'textarea',
          ];
        }
      }
    }
    /*
     * @todo: when 7.2 is more prevalent consider TypeHinting the return as
     * well.
     */
    return $jira_ticket_form;
  }
}