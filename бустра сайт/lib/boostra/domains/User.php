<?php

namespace boostra\domains;

/**
 * @property $id                              int
 * @property $maratorium_id                   int
 * @property $maratorium_date                 string
 * @property $first_loan                      int
 * @property $first_loan_amount               int
 * @property $first_loan_period               int
 * @property $service_recurent                int
 * @property $service_sms                     int
 * @property $service_insurance               int
 * @property $service_reason                  int
 * @property $service_doctor                  int
 * @property $email                           string
 * @property $password                        string
 * @property $name                            string
 * @property $group_id                        int
 * @property $enabled                         int
 * @property $last_ip                         string
 * @property $reg_ip                          string
 * @property $created                         int
 * @property $personal_data_added             int
 * @property $personal_data_added_date        string
 * @property $address_data_added              int
 * @property $address_data_added_date         string
 * @property $accept_data_added               int
 * @property $accept_data_added_date          string
 * @property $additional_data_added           int
 * @property $additional_data_added_date      string
 * @property $files_added                     int
 * @property $files_added_date                string
 * @property $card_added                      int
 * @property $card_added_date                 string
 * @property $stage_sms_sended                int
 * @property $lastname                        string
 * @property $firstname                       string
 * @property $patronymic                      string
 * @property $gender                          string
 * @property $birth                           string
 * @property $birth_place                     string
 * @property $phone_mobile                    string
 * @property $landline_phone                  string
 * @property $marital                         string
 * @property $passport_serial                 string
 * @property $subdivision_code                string
 * @property $passport_date                   string
 * @property $passport_issued                 string
 * @property $Snils                           string
 * @property $inn                             string
 * @property $bplace                          string
 * @property $Regindex                        string
 * @property $Regregion                       string
 * @property $Regdistrict                     string
 * @property $Regcity                         string
 * @property $Reglocality                     string
 * @property $Regstreet                       string
 * @property $Regbuilding                     string
 * @property $Reghousing                      string
 * @property $Regroom                         string
 * @property $Regregion_shorttype             string
 * @property $Regcity_shorttype               string
 * @property $Regstreet_shorttype             string
 * @property $Faktindex                       string
 * @property $Faktregion                      string
 * @property $Faktdistrict                    string
 * @property $Faktcity                        string
 * @property $Faktlocality                    string
 * @property $Faktstreet                      string
 * @property $Faktbuilding                    string
 * @property $Fakthousing                     string
 * @property $Faktroom                        string
 * @property $Faktregion_shorttype            string
 * @property $Faktcity_shorttype              string
 * @property $Faktstreet_shorttype            string
 * @property $contact_person_name             string
 * @property $contact_person_phone            string
 * @property $contact_person_relation         string
 * @property $contact_person2_name            string
 * @property $contact_person2_phone           string
 * @property $contact_person2_relation        string
 * @property $contact_person3_name            string
 * @property $contact_person3_phone           string
 * @property $contact_person3_relation        string
 * @property $employment                      string
 * @property $profession                      string
 * @property $workplace                       string
 * @property $experience                      string
 * @property $work_address                    string
 * @property $work_scope                      string
 * @property $work_staff                      string
 * @property $work_phone                      string
 * @property $workdirector_name               string
 * @property $Workindex                       string
 * @property $Workregion                      string
 * @property $Workcity                        string
 * @property $Workstreet                      string
 * @property $Workhousing                     string
 * @property $Workbuilding                    string
 * @property $Workroom                        string
 * @property $Workregion_shorttype            string
 * @property $Workcity_shorttype              string
 * @property $Workstreet_shorttype            string
 * @property $income_base                     string
 * @property $income_additional               string
 * @property $income_family                   string
 * @property $obligation                      string
 * @property $other_loan_month                string
 * @property $other_loan_count                string
 * @property $credit_history                  string
 * @property $other_max_amount                string
 * @property $other_last_amount               string
 * @property $bankrupt                        string
 * @property $education                       string
 * @property $marital_status                  string
 * @property $childs_count                    string
 * @property $have_car                        string
 * @property $social_inst                     string
 * @property $social_fb                       string
 * @property $social_vk                       string
 * @property $social_ok                       string
 * @property $site_id                         string
 * @property $partner_id                      string
 * @property $partner_name                    string
 * @property $utm_source                      string
 * @property $utm_medium                      string
 * @property $utm_campaign                    string
 * @property $utm_content                     string
 * @property $utm_term                        string
 * @property $webmaster_id                    string
 * @property $click_hash                      string
 * @property $sms                             string
 * @property $tinkoff_id                      string
 * @property $UID                             string
 * @property $UID_status                      string
 * @property $rebillId                        string
 * @property $file_uploaded                   int
 * @property $need_remove                     int
 * @property $loan_history                    string
 * @property $fake_order_error                int
 * @property $choose_insure                   int
 * @property $cdoctor_level                   int
 * @property $cdoctor_pdf                     string
 * @property $identified_phone                string
 * @property $scorista_history_loaded         int
 * @property $use_b2p                         int
 * @property $missing_manager_id              int
 * @property $missing_status                  int
 * @property $missing_status_date             string
 * @property $missing_real_date               string
 * @property $sentData                        int
 * @property $files_checked                   int
 * @property $last_lk_visit_time              string
 * @property $skip_credit_rating              string
 * @property $date_skip_cr_visit              string
 * @property $quantity_loans                  string
 * @property $blocked                         int
 * @property $timezone_id                     int
 * @property $call_status                     int
 * @property $continue_order                  int
 * @property $missing_manager_update_date     string
 * @property $stage_in_contact                int
 * @property $cdoctor_last_graph_update_date  string
 * @property $cdoctor_last_graph_display_date string
 * @property $registration_address_id         int|null
 * @property $factual_address_id              int|null
 *
 * @property string          $fio
 */
class User extends \boostra\domains\abstracts\EntityObject{
    
    public static function table(): string
    {
        return 's_users';
    }
    
    public function init()
    {
        $this->fio = $this->lastname . ' ' . $this->firstname . ' ' .$this->patronymic;
    }
    
    public static function _getColumns(): array
    {
        return [
            'id',
            'maratorium_id',
            'maratorium_date',
            'first_loan',
            'first_loan_amount',
            'first_loan_period',
            'service_recurent',
            'service_sms',
            'service_insurance',
            'service_reason',
            'service_doctor',
            'email',
            'password',
            'name',
            'group_id',
            'enabled',
            'last_ip',
            'reg_ip',
            'created',
            'personal_data_added',
            'personal_data_added_date',
            'address_data_added',
            'address_data_added_date',
            'accept_data_added',
            'accept_data_added_date',
            'additional_data_added',
            'additional_data_added_date',
            'files_added',
            'files_added_date',
            'card_added',
            'card_added_date',
            'stage_sms_sended',
            'lastname',
            'firstname',
            'patronymic',
            'gender',
            'birth',
            'birth_place',
            'phone_mobile',
            'landline_phone',
            'marital',
            'passport_serial',
            'subdivision_code',
            'passport_date',
            'passport_issued',
            'Snils',
            'inn',
            'bplace',
            'Regindex',
            'Regregion',
            'Regdistrict',
            'Regcity',
            'Reglocality',
            'Regstreet',
            'Regbuilding',
            'Reghousing',
            'Regroom',
            'Regregion_shorttype',
            'Regcity_shorttype',
            'Regstreet_shorttype',
            'Faktindex',
            'Faktregion',
            'Faktdistrict',
            'Faktcity',
            'Faktlocality',
            'Faktstreet',
            'Faktbuilding',
            'Fakthousing',
            'Faktroom',
            'Faktregion_shorttype',
            'Faktcity_shorttype',
            'Faktstreet_shorttype',
            'contact_person_name',
            'contact_person_phone',
            'contact_person_relation',
            'contact_person2_name',
            'contact_person2_phone',
            'contact_person2_relation',
            'contact_person3_name',
            'contact_person3_phone',
            'contact_person3_relation',
            'employment',
            'profession',
            'workplace',
            'experience',
            'work_address',
            'work_scope',
            'work_staff',
            'work_phone',
            'workdirector_name',
            'Workindex',
            'Workregion',
            'Workcity',
            'Workstreet',
            'Workhousing',
            'Workbuilding',
            'Workroom',
            'Workregion_shorttype',
            'Workcity_shorttype',
            'Workstreet_shorttype',
            'income_base',
            'income_additional',
            'income_family',
            'obligation',
            'other_loan_month',
            'other_loan_count',
            'credit_history',
            'other_max_amount',
            'other_last_amount',
            'bankrupt',
            'education',
            'marital_status',
            'childs_count',
            'have_car',
            'social_inst',
            'social_fb',
            'social_vk',
            'social_ok',
            'site_id',
            'partner_id',
            'partner_name',
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_content',
            'utm_term',
            'webmaster_id',
            'click_hash',
            'sms',
            'tinkoff_id',
            'UID',
            'UID_status',
            'rebillId',
            'file_uploaded',
            'need_remove',
            'loan_history',
            'fake_order_error',
            'choose_insure',
            'cdoctor_level',
            'cdoctor_pdf',
            'identified_phone',
            'scorista_history_loaded',
            'use_b2p',
            'missing_manager_id',
            'missing_status',
            'missing_status_date',
            'missing_real_date',
            'sentData',
            'files_checked',
            'last_lk_visit_time',
            'skip_credit_rating',
            'date_skip_cr_visit',
            'quantity_loans',
            'blocked',
            'timezone_id',
            'call_status',
            'continue_order',
            'missing_manager_update_date',
            'stage_in_contact',
            'cdoctor_last_graph_update_date',
            'cdoctor_last_graph_display_date',
            'registration_address_id',
            'factual_address_id'
        ];
    }

}