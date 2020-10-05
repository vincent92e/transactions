<?php

namespace Drupal\transactions\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Render\FormattableMarkup;

/**
 * Class ViewTransactions.
 */
class ViewTransactions extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'view_transactions';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    //Attach libraries
    $form['#attached']['library'][] = 'core/drupal.ajax';
    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    // Get current path
    $path = $this->getPath();


    if ($path != '/transactions') {
      $pageTitle = 'Recent Activity';
    }
    else {
      $pageTitle = 'All Transactions';

      $form['filter'] = [
        '#type' => 'container',
        '#prefix' => '<div class="form-row">',
        '#suffix' => '</div>',
        '#theme_wrappers' => [],
      ];

      $form['filter']['icon'] = [
        '#type' => 'container',
        '#prefix' => '<div class="col-sm-6 col-md-5 form-group mb-4 position-relative">',
        '#suffix' => '</div>',
        '#theme_wrappers' => [],
      ];

      $form['filter']['icon']['daterange'] = [
        '#type' => 'textfield',
        '#value' => $this->t('14/01/2020 - 15/02/2020'),
        '#attributes' => [
          'class' => ['form-control', 'mt-0', 'trans-date', 'black-shade6'],
        ],
        '#theme_wrappers' => [],
      ];

      $form['filter']['icon']['cal'] = [
        '#type' => 'item',
        '#markup' => '<i class="fa fa-calendar"></i>',
        '#prefix' => '<span class="cal-icon font-size-18 grey-shade3">',
        '#suffix' => '</span>',
        '#theme_wrappers' => [],
      ];

      $form['filter']['all_filters'] = [
        '#type' => 'container',
        '#prefix' => '<div class="col d-flex align-items-center mr-auto form-group">',
        '#suffix' => '</div>',
        '#theme_wrappers' => [],
      ];

      $form['filter']['all_filters']['slider'] = [
        '#type' => 'item',
        '#markup' => '<i class="fa fa-sliders font-size-18 ml-1"></i>',
        '#prefix' => '<a class="mb-2 payyed-green allfilters cursor-p">All Filters',
        '#suffix' => '</a>',
        '#theme_wrappers' => [],
      ];

      // Radio options
      $options = [
        'all' => $this->t('All Transactions'),
        'payment sent' => $this->t('Payments Sent'),
        'payment received' => $this->t('Payments Received'),
        'refund' => $this->t('Refunds'),
        'withdraw' => $this->t('Withdrawal'),
        'deposit' => $this->t('Deposit'),
      ];
      $form['filter']['trans_type'] = [
        '#type' => 'radios',
        '#options' => $options,
        '#prefix' => '<div class="col-12 transaction-types mb-3">',
        '#suffix' => '</div>',
        '#default_value' => 'all',
        '#theme_wrappers' => [],
        '#ajax' => [
          'callback' => '::filterTransactions',
          'wrapper' => 'transaction-table',
          'progress' => [
            'message' => '',
          ],
        ],
      ];

      // Ajax search
      $form['filter']['submit'] = [
        '#type' => 'submit',
        '#value' => 'Submit',
        '#attributes' => [
          'class' => ['daterangesubmit', 'd-none'],
        ],
        '#ajax' => [
          'callback' => '::filterTransactions',
          'wrapper' => 'transaction-table',
          'progress' => [
            'message' => '',
          ],
        ],
      ];
    }


    $form['data'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['bg-light', 'box-shadow-sm', 'rounded', 'py-4', 'mb-4'],
      ],
    ];

    // Table header
    $header = [
      'date' => $this->t('Date'),
      'description' => $this->t('Description'),
      'status' => $this->t('Status'),
      'amount' => $this->t('Amount'),
    ];

    $form['data']['page_title'] = [
      '#type' => 'item',
      '#markup' => $this->t('@title', ['@title' => $pageTitle]),
      '#theme_wrappers' => [],
    ];

    $form['data']['table_cont'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => ['ejike'],
      ],
    ];

    if (!empty($form_state->getUserInput()['daterange'])) {

      $transaction_type = $form_state->getUserInput()['trans_type'];
      $dateString = $form_state->getUserInput()['daterange'];
      $dateArray = explode(" ", $dateString);
      $startDate = $this->reverseDate($dateArray[0]);
      $endDate = $this->reverseDate($dateArray[2]);
      $noData = $this->t('No users found');

      $form['data']['table_cont']['table'] = [
        '#type' => 'tableselect',
        '#title' => $this->t('List of email subscribers'),
        '#header' => $header,
        '#options' => $this->getRows($startDate, $endDate, $transaction_type),
        '#empty' => new FormattableMarkup('<div class="d-flex"><div class="mx-auto">@noData</div></div>', ['@noData' => $noData]),
        '#attributes' => [
          'id' => 'transaction-table',
          'class' => ['w-100'], //changed
        ],
      ];
    }
    else {
      $form['data']['table_cont']['table'] = [
        '#type' => 'tableselect',
        '#title' => $this->t('List of email subscribers'),
        '#header' => $header,
        '#options' => $this->getRows(),
        '#empty' => $this->t('No users found'),
        '#attributes' => [
          'id' => 'transaction-table',
          'class' => ['w-100'], //changed
        ],
      ];
    }

    if ($path != '/transactions') {
      $form['data']['view_all'] = [
        '#type' => 'item',
        '#markup' => '<a href="#" class="font-weight-400 payyed-green font-size-16">'.$this->t('View all').'<i class="fa fa-chevron-right font-size-14 ml-2"></i></a>',
        '#prefix' => '<div class="text-center mt-4">',
        '#suffix' => '</div>',
        '#theme_wrappers' => [],
      ];
    }
    else {
      $form['data']['pager'] = array(
        '#type' => 'pager',
        '#prefix' => '<div class="text-center mt-4">',
        '#suffix' => '</div>',
      );
    }

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

  }

  /**
   * Get status icons
   * @param $var
   * @return string
   */
  public function getStatusClass($var) {

    switch ($var) {
      case 'completed':
        return "fa-check-circle payyed-green";
        break;
      case 'waiting':
        return "fa-ellipsis-h payyed-warning";
        break;
      case 'cancelled':
        return "fa-times-circle payyed-cancelled";
        break;
      case 'refund':
        return "fa-repeat payyed-return";
        break;
      default:
        return "";
    }
  }

  /**
   * Get transaction sign
   * @param $var
   * @return string
   */
  public function getSign($var) {

    switch ($var) {
      case 'withdraw':
      case 'payment sent':
        return "-";
        break;
      case 'payment received':
      case 'refund':
        return "+";
        break;
      default:
        return "";
    }
  }

  /**
   * Get current path
   * @return mixed
   */
  public function getPath() {
    // Get the current path.
    return $current_path = \Drupal::service('path.current')->getPath();;
  }

  /**
   * Fetch table rows
   * @param null $startDate
   * @param null $endDate
   * @param string $transaction_type
   * @return array
   */
  public function getRows($startDate = NULL, $endDate = NULL, $transaction_type = 'all') {
    $current_user_id = \Drupal::currentUser()->id();
    $path = $this->getPath();


    // Load data from database
    $database = \Drupal::database();
    $query = $database->select('payyed_transactions', 'pt');
    $query->fields('pt', ['rid','uid','name', 'description', 'transaction_id', 'amount', 'fee', 'transaction_type', 'date', 'status']);
    $query->condition('pt.uid', $current_user_id);
    if (!empty($startDate)) {
      $query->condition('pt.date', strtotime($startDate), '>=');
    }
    if (!empty($endDate)) {
      $query->condition('pt.date', strtotime($endDate), '<=');
    }
    if (($transaction_type != 'all')) {
      $query->condition('pt.transaction_type', $transaction_type);
    }
    if ($path != '/transactions') {
      $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(7);
    } else {
      $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(8);
    }

    $results = $pager->execute()->fetchAll();


    $data = [];
    // Next, loop through the $results array
    foreach ($results as $result) {
      if (!empty($result->uid)) {
        $day = date('d', $result->date);
        $month = date('M', $result->date);
        $desc_name = $result->name;
        $desc_type = $result->description;
        $status_class = $this->getStatusClass($result->status);
        $sign = $this->getSign($result->transaction_type);
        $amount = $sign.' £'.$result->amount;
        // Get site front page
        $site_url = \Drupal::urlGenerator()->generateFromRoute('<front>', [], ['absolute' => TRUE]);
        $transaction_url = $site_url.'view/transaction/'.$result->transaction_id;
        $data[] = [
          'date' =>  new FormattableMarkup('<div class="date"><span class="d-block font-weight-300 font-size-18">@day</span><span class="d-block font-weight-300 font-size-12 text-uppercase">@month</span></div>',['@day' => $day, '@month' => $month]),
          'description' =>  new FormattableMarkup('<div class="description"><span class="d-block font-size-18">@name</span><span class="d-block black-shade3">@type</span></div><a class="use-ajax" data-toggle="modal" data-dialog-type="modal" href="@transaction_url" data-dialog-options="{&quot;width&quot;:620}"></a>',['@name' => $desc_name, '@type' => $desc_type, '@transaction_url' => $transaction_url]),
          'status' => new FormattableMarkup('<i class="fa @statusClass font-size-16 my-2 font-weight-900"></i>', ['@statusClass' => $status_class]),
          'amount' => new FormattableMarkup('<div class="amount"><span class="d-inline-block text-nowrap">@amount</span><span class="d-inline-block font-size-14 text-uppercase ml-1">(GBP)</span></div>', ['@amount' => $amount]),
        ];
      }
    }

    return $data;
  }

  /**
   * Reverse date string
   * @param $var
   * @return string
   */
  public function reverseDate($var) {
    $date = explode("/", $var);
    return $date[2].'-'.$date[1].'-'.$date[0];
  }

  /**
   * Callback to filter table data
   * @param array $form
   * @param FormStateInterface $form_state
   * @return mixed
   */
  public  function filterTransactions(array &$form, FormStateInterface $form_state) {
    return $form['data']['table_cont']['table'];
  }
}
