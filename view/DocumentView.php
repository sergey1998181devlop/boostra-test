<?php

use boostra\services\UsersAddressService;

error_reporting(0);
ini_set('display_errors', 'Off');
require_once 'View.php';

class DocumentView extends View
{
    public function fetch()
    {
        $action = $this->request->get('action', 'string');

        if ($action == 'preview')
        {
            $this->preview_action();
        } elseif ($action === 'creditworthiness_assessment') {
            $this->showCreditworthinessAssessmentDocument();
            return false;
        }

        $this->show_unaccepted_agreement_modal();

        $id = $this->request->get('id');
        $id = str_replace('.pdf', '', $id);
        $from_crm = $this->request->get('from_crm');

        if (empty($id)) {
            return false;
        }
        if (!($user_id = $this->request->get('user_id', 'integer'))) {
            return false;
        }
        if (!($document = $this->documents->get_document($id))) {
            return false;
        }
        if ($user_id != $document->user_id) {
            return false;
        }
        if (!($user = $this->users->get_user((int)$document->user_id))) {
            return false;
        }
        if (!empty($user->blocked) && empty($from_crm)) {
            return false;
        }
        if (!empty($document->params))
        {
            foreach ($document->params as $param_name => $param_value) {
                $this->design->assign($param_name, $param_value);
            }
        }

        if (!empty($document->organization_id)) {
            $this->design->assign('organization', $this->organizations->get_organization($document->organization_id));
        }

        $this->design->assign('document_id', $document->id);
        $this->design->assign('document_created', $document->created);
        
       	$tpl = $this->design->fetch('pdf/'.$document->template);
        $this->pdf->create($tpl, $document->name, str_replace('.tpl', '.pdf', $document->template));

        return true;
    }
    
    private function preview_action()
    {
        $type = $this->request->get('document');
        $params = $this->request->get('params');
        $organization_id = $this->request->get('organization_id');


        $user_id = $this->request->get('user_id', 'integer') ?: (int)$_SESSION['user_id'];
        $hide_user_data = $params['hide_user_data'] ?? false;

        if ($user_id && $user = $this->users->get_user($user_id)) {

            if ($type == 'agreement_disagreement_to_receive_ko') {
                $params = $this->request->get('params');
                $sms = !empty($params['asp']) ? $params['asp'] : null;
                $sign_date = !empty($params['sign_date']) ? $params['sign_date'] : null;

                if ($order = $this->orders->get_last_order($user->id)) {
                    $doc_params = $this->docs->getOfferAgreementParams($user, $order->id, $sms, $sign_date);
                } else {
                    $organisation = $this->organizations->get_organization($this->organizations->get_base_organization_id());
                    $user_passport_split = $this->helpers->splitPassportSerial($user->passport_serial);

                    $addressReg = trim(sprintf(
                        '%s %s, %s, ул. %s, д. %s, кв. %s',
                        $user->Regindex,
                        $user->Regregion,
                        $user->Regcity,
                        $user->Regstreet,
                        $user->Reghousing,
                        $user->Regroom
                    ));

                    $doc_params = [
                        'full_name' => $this->helpers->getFIO($user),
                        'email' => $user->email,
                        'phone' => $user->phone_mobile,
                        'passport_serial' => $user_passport_split['serial'],
                        'passport_number' => $user_passport_split['number'],
                        'passport_date' => $user->passport_date,
                        'passport_issued' => $user->passport_issued,
                        'subdivision_code' => $user->subdivision_code,
                        'registration_address' => $addressReg,
                        'organization_name' => $organisation->name,
                        'organization_ogrn' => $organisation->ogrn,
                        'zaim_number' => null,
                        'zaim_date' => null,
                        'organization_site' => $organisation->site,
                        'organization_address_post' => $organisation->address,
                        'organization_director' => $organisation->director ?? 'Поздняковa С.В.',
                        'organization_address_req' => $organisation->address,
                        'organization_email' => $organisation->email,
                        'plaintiff_name' => 'ООО ПКО "ПРАВОВАЯ ЗАЩИТА"',
                        'plaintiff_site' => 'https://pravza.com/',
                        'accept_sms' => $sms,
                        'order_signed' => false,
                        'organization_id' => $organisation->id,
                        'sign_date' => $sign_date,
                    ];
                }
                $this->design->assignBulk($doc_params);
            }

            $user = (array)$user;
            foreach ($user as $item_name => $item_value) {
                $user_field = $hide_user_data ? str_replace(str_split((string)$item_value), '_', $item_value) : $item_value;
                $this->design->assign($item_name, $user_field);
            }
        }

        if (in_array(strtoupper($type), ['agreement_disagreement_to_receive_ko'])) {
            $organization_id = $this->organizations->get_base_organization_id();
        }

        if (empty($organization_id) && !empty($params['contract_number'])) {
            $contract = $this->contracts->get_contract_by_params(['number' => $params['contract_number']]);
            if ($contract && !empty($contract->organization_id)) {
                $organization_id = $contract->organization_id;
            }
        }

        if (!empty($organization_id)) {
            $this->design->assign('organization', $this->organizations->get_organization($organization_id));
        }

        if (!($template = $this->documents->get_document_param('PREVIEW_'.strtoupper($type))))
            return false;
//echo __FILE__.' '.__LINE__.'<br /><pre>';var_dump($template);echo '</pre><hr />';exit;
        if (!empty($params))
        {
            foreach ($params as $param_name => $param_value)
                $this->design->assign($param_name, $param_value);
        }

       	$tpl = $this->design->fetch('pdf/'.$template['template']);
//echo $template['template'];exit;        
        
        $this->pdf->create($tpl, $template['name'], str_replace('.tpl', '.pdf', $template['template']));

        return true;    	
    }

    /**
     * Отображение "Лист оценки платежеспособности заемщика"
     *
     * @return void
     */
    private function showCreditworthinessAssessmentDocument(): void
    {
        $from_crm = $this->request->get('from_crm');

        if (empty($from_crm)) {
            return;
        }

        $user_id = $this->request->get('user_id', 'integer');

        if (empty($user_id)) {
            return;
        }

        $user = $this->users->get_user($user_id);

        if (empty($user)) {
            return;
        }

        $order_id = $this->request->get('id');

        if (empty($order_id)) {
            return;
        }

        $order = $this->orders->get_order($order_id);

        if (empty($order)) {
            return;
        }

        $contract = $this->contracts->get_contract((int)$order->contract_id);

        if (empty($contract)) {
            return;
        }

        $pdnCalculations = $this->getPdnCalculationsByOrderId([(int)$order->id], (int)$order->organization_id);

        if (empty($pdnCalculations[0])) {
            return;
        }

        $pdnCalculation = $pdnCalculations[0];

        // Типы расчетов ПДН, для которых есть документ "Лист оценки платежеспособности заемщика"
        $necessaryPdnCalculationTypes = [3, 4];
        if (!in_array($pdnCalculation->pdn_calculation_type, $necessaryPdnCalculationTypes)) {
            return;
        }

        $data = [
            'full_name' => "{$user->lastname} {$user->firstname} {$user->patronymic}",
            'loan_amount' => number_format($pdnCalculation->amount ?? $contract->amount, 0, ',', ' '),
            'period' => $order->period,
            'percent' => number_format((float)$order->percent ?: 0.8, 2, ',', ' '),
            'age' => $this->getUserAge($user->birth),
            'reg_address' => $this->getUserRegAddress($user),
            'fakt_address' => $this->getUserFaktAddress($user, $pdnCalculation),
            'phone_mobile' => '+' . $user->phone_mobile,
            'passport' => $this->getUserPassport($user),
            'income' => !empty($pdnCalculation->income_base) ? $pdnCalculation->income_base : $user->income_base,
            'approved_loan_amount' => number_format($contract->amount, 0, ',', ' '),
            'issuance_date' => date('d.m.Y', strtotime($contract->issuance_date)),
            'verificator' => $this->getManager((int)$order->manager_id),
            'profession' => $user->profession,
            'workplace' =>  $user->workplace,
        ];

        $this->design->assignBulk($data);

        if ($pdnCalculation !== null && !empty($pdnCalculation->pdn)) {
            $this->design->assign('pdn', number_format($pdnCalculation->pdn, 1, ',', ' '));
        }

        $this->pdf->create(
            $this->design->fetch( 'pdf/creditworthiness_assessment.tpl' ),
            'Лист оценки платежеспособности заемщика',
            'Лист оценки платежеспособности заемщика.pdf'
        );

        exit();
    }

    /**
     * @param array $ordersId
     * @param int $organizationId
     * @return false|array|null
     */
    public function getPdnCalculationsByOrderId(array $ordersId, int $organizationId = Organizations::AKVARIUS_ID)
    {
        if ($this->organizations->isFinlab($organizationId)) {
            $tableName = 'pdn_calculation_finlab';
        } else {
            $tableName = 'pdn_calculation';
        }

        $query = $this->db->placehold(
            'SELECT * FROM ' . $tableName . ' 
            WHERE order_id IN (?@)',
            $ordersId
        );

        $this->db->query($query);
        return $this->db->results();
    }

    private function getUserAge(?string $birthDate): ?int
    {
        if ($birthDate === null) {
            return null;
        }

        $birthDate = DateTime::createFromFormat('d.m.Y', $birthDate);
        $today = new DateTime();

        return $birthDate ? $birthDate->diff($today)->y : null;
    }

    private function getUserRegAddress(stdClass $user): string
    {
        $address = $user->Regindex . ', ' .
            $user->Regregion . ' ' . $user->Regregion_shorttype . ', ' .
            $user->Regcity_shorttype . ' ' . $user->Regcity . ', ' .
            $user->Regstreet_shorttype . ' ' . $user->Regstreet;

        if ($user->Reghousing) {
            $address .= ', д. ' . $user->Reghousing;
        }

        if ($user->Regbuilding) {
            $address .= ', стр. ' . $user->Regbuilding;
        }

        if ($user->Regroom) {
            $address .= ', кв. ' . $user->Regroom;
        }

        return $address;
    }

    private function getUserFaktAddress(stdClass $user, object $pdnCalculation): string
    {
        // Если для заявки есть адрес проживания необходимый для расчета ПДН, то отображаем его
        if (!empty($pdnCalculation->fakt_address)) {
            $faktAddressFromPdn = json_decode($pdnCalculation->fakt_address);

            if (!empty($faktAddressFromPdn)) {
                if (file_exists(__DIR__ . '/../lib/autoloader.php')) {
                    require_once __DIR__ . '/../lib/autoloader.php';
                    (new UsersAddressService())->addFactualAddressToUser($user, $faktAddressFromPdn);
                }
            }
        }

        $address = '';

        if ($user->Faktindex) {
            $address .= $user->Faktindex . ', ';
        }

        $address .= $user->Faktregion . ' ' . $user->Faktregion_shorttype . ', ' .
            $user->Faktcity_shorttype . ' ' . $user->Faktcity . ', ' .
            $user->Faktstreet_shorttype . ' ' . $user->Faktstreet;

        if ($user->Fakthousing) {
            $address .= ', д. ' . $user->Fakthousing;
        }

        if ($user->Faktbuilding) {
            $address .= ', стр. ' . $user->Faktbuilding;
        }

        if ($user->Faktroom) {
            $address .= ', кв. ' . $user->Faktroom;
        }

        return $address;
    }

    private function getUserPassport(stdClass $user): string
    {
        $passportNumber = substr($user->passport_serial, -6);
        $passportSerial = str_replace([$passportNumber, ' '], '', $user->passport_serial);

        return 'серия ' . $passportSerial . ' № ' . $passportNumber . ' Выдан ' . $user->passport_issued . ' от ' . $user->passport_date;
    }

    private function getManager(int $managerId): string
    {
        if ($managerId === $this->managers::MANAGER_SYSTEM_ID) {
            return 'Система';
        }

        $manager = $this->managers->get_manager($managerId);
        return !empty($manager) && !empty($manager->name) ? $manager->name : '';
    }
}
