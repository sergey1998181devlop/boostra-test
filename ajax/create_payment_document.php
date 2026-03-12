<?php
require_once('../api/Simpla.php');
$simpla = new Simpla();

$type = $simpla->documents::SPRAVKA_O_POGASHENII_ZAIMA;
$paymentId = $simpla->request->get('payment_id');
$getPayment = $simpla->best2pay->get_payment($paymentId);
$contractNumber = $getPayment->contract_number;
$getDocument = $simpla->documents->getDocument($type,$contractNumber);

if (!empty($getDocument)) {
    print json_encode([
        'user_id' => $getDocument->user_id,
        'document_id' =>$getDocument->id
    ]);
}else{
    $user_id = $getPayment->user_id;
    $user = $simpla->users->get_user((int)$user_id);

    if( !empty($user->loan_history )){

        $loans =  array_filter( $user->loan_history, static function( $loan ){
            return ! empty( $loan->close_date );
        } );
        $foundIndex = -1;
        foreach ($loans as $index => $loan) {
            if ($loan->number === $contractNumber) {
                $foundIndex = $index;
                break;
            }
        }

        $document_id = $simpla->documents->create_document( [
            'type'               => $simpla->documents::SPRAVKA_O_POGASHENII_ZAIMA,
            'notification_title' => "Уведомление о выплате займа № $contractNumber",
            'user_id'            => $user_id,
            'contract_number'    => $contractNumber,
            'params'             => array_merge(
                (array) $loans[$foundIndex],
                [
                    'accept_data_added_date' => $user->accept_data_added_date,
                    'lastname'               => $user->lastname,
                    'firstname'              => $user->firstname,
                    'patronymic'             => $user->patronymic,
                    'birth'                  => $user->birth,
                    'registration_address'   => $user->registration_address,
                    'gender'                 => $user->gender,
                    'root_url'               => $simpla->config->root_url,
                    'amount_in_string'       => $simpla->documents->convertAmountToString($loans[$foundIndex]->amount) ,
                ]
            )
        ] );

        $file_url    = $simpla->config->root_url . '/document/' . $user_id . '/' . $document_id;
        $storage_uid = $simpla->filestorage->upload_file( $file_url, 15 );

        $simpla->documents->update_document($document_id, [
            'filestorage_uid' => $storage_uid,
        ]);

        print json_encode([
            'user_id' => $user_id,
            'document_id' => $document_id
        ]);
    }

}
