<?php

namespace Drupal\currencies_list\Forms;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class AdminCurrenciesForm extends ConfigFormBase
{

    protected function getEditableConfigNames()
    {
        return ['currencies_list.currencies_list'];
    }

    public function getFormId()
    {
        return 'admin_currencies_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $config = $this->config('currencies_list.currencies_list');

        $form['api_key'] = [
            '#type'          => 'textfield',
            '#title'         => $this->t('API Key'),
            '#default_value' => $config->get('api_key'),
            '#required'      => true,
            '#description'   => $this->t(
                'Enter the API key used to access the currencies list. Please get an API key at <a href="https://freecurrencyapi.com">freecurrencyapi.com</a>.'
            ),
        ];
        $form['api_url'] = [
            '#type'          => 'textfield',
            '#title'         => $this->t('API URL'),
            '#default_value' => $config->get('api_url'),
            '#required'      => true,
            '#description'   => $this->t("Ente the base API endpoint URL."),
            '#weight'        => -1,
        ];

        return parent::buildForm(
            $form, $form_state
        );
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $this->config('currencies_list.currencies_list')->set(
            'api_key', $form_state->getValue('api_key')
        )->save();
        $this->config('currencies_list.currencies_list')->set(
            'api_url', $form_state->getValue('api_url')
        )->save();
        parent::submitForm(
            $form, $form_state
        );
    }


}