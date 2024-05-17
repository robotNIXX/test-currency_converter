<?php

namespace Drupal\currencies_list\Forms;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\currencies_list\Services\CurrenciesApiService;

class AdminCurrenciesListForm extends ConfigFormBase
{

    /**
     * Gets the configuration names that will be editable.
     *
     * @return array
     *   An array of configuration object names that are editable if called in
     *   conjunction with the trait's config() method.
     */
    protected function getEditableConfigNames()
    {
        return ['currencies_list.currencies_list'];
    }

    /**
     * Returns a unique string identifying the form.
     *
     * The returned ID should be a unique string that can be a valid PHP function
     * name, since it's used in hook implementation names such as
     * hook_form_FORM_ID_alter().
     *
     * @return string
     *   The unique string identifying the form.
     */
    public function getFormId()
    {
        return 'admin_currencies_list_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state) {
        $currencies = CurrenciesApiService::makeRequest('GET', 'currencies');

        $currencies_list = [];
        foreach ($currencies as $currency) {
            $currencies_list[$currency->code] = $currency->name;
        }
        $config = $this->config('currencies_list.currencies_list');

        $form['available_currencies'] = [
            '#type' => 'select',
            '#title' => $this->t('Currencies'),
            '#options' => $currencies_list,
            '#default_value' => $config->get('available_currencies'),
            '#required' => TRUE,
            '#multiple' => TRUE,
        ];

        return parent::buildForm(
            $form, $form_state
        );
    }

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $currencies = $form_state->getValue('available_currencies');
        $this->config('currencies_list.currencies_list')
            ->set('available_currencies', $currencies)
            ->save();

        CurrenciesApiService::updateRates();
        parent::submitForm(
            $form, $form_state
        );
    }
}