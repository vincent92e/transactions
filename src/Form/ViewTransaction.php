<?php

namespace Drupal\transactions\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ViewTransactions.
 */
class ViewTransaction extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'view_transaction';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $tid = NULL) {

    $current_user_id = \Drupal::currentUser()->id();

    // Load data from database
    $database = \Drupal::database();
    $query = $database->select('payyed_transactions', 'pt');
    $query->fields('pt', ['rid','uid','name', 'description', 'transaction_id', 'amount', 'fee', 'transaction_type', 'date', 'status']);
    $query->condition('pt.uid', $current_user_id);
    $query->condition('pt.transaction_id', $tid);
    $results = $query->execute()->fetchAll();

    $payment_amount = $results[0]->amount;
    $fee = $results[0]->fee;
    $total_amount = $payment_amount - $fee;
    $payed_by = $results[0]->name;
    $transaction_id = $results[0]->transaction_id;
    $description = $results[0]->description;
    $status = $results[0]->status;
    $day = date('d', $results[0]->date);
    $month = date('M', $results[0]->date);
    $year  = date('Y', $results[0]->date);
    $date = $day.' '.$month.' '.$year;


    // Attach libraries
    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';


    // Build transaction form
    $form['title'] = [
      '#type' => 'item',
      '#markup' => $this->t('Transaction Details'),
      '#prefix' => '<h4 class="font-weight-400 my-3">',
      '#suffix' => '</h4>',
    ];

    $form['payment_amount'] = [
      '#type' => 'item',
      '#markup' => '<span>'.'£'.$payment_amount.'</span>',
    ];

    $form['fee'] = [
      '#type' => 'item',
      '#markup' => '<span>'.'-'.'£'.$fee.'</span>',
    ];

    $form['total_amount'] = [
      '#type' => 'item',
      '#markup' => '<span>'.'£'.number_format($total_amount, 2).'</span>',
    ];

    $form['payed_by'] = [
      '#type' => 'item',
      '#markup' => '<span>'.$payed_by.'</span>',
    ];

    $form['transaction_id'] = [
      '#type' => 'item',
      '#markup' => '<span>'.$transaction_id.'</span>',
    ];

    $form['description'] = [
      '#type' => 'item',
      '#markup' => '<span>'.$description.'</span>',
    ];

    $form['status'] = [
      '#type' => 'item',
      '#markup' => '<span>'.$status.'</span>',
    ];

    $form['date'] = [
      '#type' => 'item',
      '#markup' => '<span>'.$date.'</span>',
    ];



    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format'?$value['value']:$value));
    }
  }

}
