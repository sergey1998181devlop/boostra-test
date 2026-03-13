<?php

namespace Feature\scorings;

use Location;
use PHPUnit\Framework\TestCase;

class LocationTest extends TestCase
{
    private Location $location;
    private int $registrationAdressId;
    private int $factualAddressId;
    private int $userId;
    private int $orderId;
    private int $scoringId;

    protected function setUp(): void
    {
        require_once __DIR__ . '/../../../api/Simpla.php';
        require_once __DIR__ . '/../../../scorings/Location.php';
        require_once __DIR__ . '/../../../lib/autoloader.php';

        $this->location = new Location();

        if (!$this->location->config->is_dev) {
            echo('Error: Application is not in development mode!');
            die();
        }
    }

    public function testRun_scoring_success()
    {
        $this->addAvailableRegistrationAddressId();
        $this->addAvailableFactualAddressId();
        $result = $this->execute();

        $this->assertTrue($result['success']);
        $this->assertStringStartsWith('Допустимый регион', $result['string_result']);
    }

    public function testRun_scoring_fakt_fail()
    {
        $this->addUnavailableRegistrationAddressId();
        $this->addAvailableFactualAddressId();
        $result = $this->execute();

        $this->assertFalse($result['success']);
        $this->assertStringStartsWith('Недопустимый регион', $result['string_result']);
    }

    public function testRun_scoring_reg_fail()
    {
        $this->addAvailableRegistrationAddressId();
        $this->addUnavailableFactualAddressId();
        $result = $this->execute();

        $this->assertFalse($result['success']);
        $this->assertStringStartsWith('Недопустимый регион', $result['string_result']);
    }

    public function testRun_scoring_reg_and_fakt_fail()
    {
        $this->addUnavailableRegistrationAddressId();
        $this->addUnavailableFactualAddressId();
        $result = $this->execute();

        $this->assertFalse($result['success']);
        $this->assertStringStartsWith('Недопустимый регион', $result['string_result']);
    }

    public function execute(): array
    {
        $this->addUser();
        $this->addOrder();
        $this->addScoring();

        return $this->location->run_scoring($this->scoringId);
    }

    private function addAvailableRegistrationAddressId(): void
    {
        $query = "INSERT INTO users_addresses (address_index, region, region_code, district, city, locality, street, building, housing, room, region_shorttype, city_shorttype, street_shorttype, fias_id) VALUES ('111111', 'Москва', '77', '', 'Москва', '', 'Молодежная', '', '1', '2', 'г', 'г', 'ул', '6f13e70c-32b0-4754-abcd-29d4bd0fc27c');";

        $this->location->db->query($query);

        $this->registrationAdressId = $this->location->db->insert_id();
    }

    private function addAvailableFactualAddressId(): void
    {
        $query = "INSERT INTO users_addresses (address_index, region, region_code, district, city, locality, street, building, housing, room, region_shorttype, city_shorttype, street_shorttype, fias_id) VALUES ('111112', 'Московская область', '77', '', 'Щелково', '', 'Летная', '', '3', '4', 'г', 'г', 'ул', '6f13e70c-32b0-4754-abcd-29d4bd0fc27c');";

        $this->location->db->query($query);

        $this->factualAddressId = $this->location->db->insert_id();
    }

    private function addUnavailableRegistrationAddressId(): void
    {
        $query = "INSERT INTO users_addresses (address_index, region, region_code, district, city, locality, street, building, housing, room, region_shorttype, city_shorttype, street_shorttype, fias_id) VALUES ('111111', 'Чеченская республика', '95', '', 'Чечня', '', 'Молодежная', '', '1', '2', 'г', 'г', 'ул', '6f13e70c-32b0-4754-abcd-29d4bd0fc27c');";

        $this->location->db->query($query);

        $this->registrationAdressId = $this->location->db->insert_id();
    }

    private function addUnavailableFactualAddressId(): void
    {
        $query = "INSERT INTO users_addresses (address_index, region, region_code, district, city, locality, street, building, housing, room, region_shorttype, city_shorttype, street_shorttype, fias_id) VALUES ('111112', 'Чеченская республика', '95', '', 'Чечня', '', 'Летная', '', '3', '4', 'г', 'г', 'ул', '6f13e70c-32b0-4754-abcd-29d4bd0fc27c');";

        $this->location->db->query($query);

        $this->factualAddressId = $this->location->db->insert_id();
    }

    private function addUser(): void
    {
        $registrationAddressId = $this->registrationAdressId;
        $factualAddressId = $this->factualAddressId;

        $query = "INSERT INTO pravza_simpla.s_users (maratorium_id, maratorium_date, first_loan, first_loan_amount, first_loan_period, service_recurent, service_sms, service_insurance, service_reason, service_doctor, email, password, name, group_id, enabled, last_ip, reg_ip, created, personal_data_added, personal_data_added_date, address_data_added, address_data_added_date, accept_data_added, accept_data_added_date, additional_data_added, additional_data_added_date, files_added, files_added_date, card_added, card_added_date, stage_sms_sended, lastname, firstname, patronymic, gender, birth, birth_place, phone_mobile, landline_phone, marital, passport_serial, subdivision_code, passport_date, passport_issued, Snils, inn, registration_address_id, factual_address_id, bplace, Regindex, Regregion, Regdistrict, Regcity, Reglocality, Regstreet, Regbuilding, Reghousing, Regroom, Regregion_shorttype, Regcity_shorttype, Regstreet_shorttype, Faktindex, Faktregion, Faktdistrict, Faktcity, Faktlocality, Faktstreet, Faktbuilding, Fakthousing, Faktroom, Faktregion_shorttype, Faktcity_shorttype, Faktstreet_shorttype, contact_person_name, contact_person_phone, contact_person_relation, contact_person2_name, contact_person2_phone, contact_person2_relation, contact_person3_name, contact_person3_phone, contact_person3_relation, employment, profession, workplace, experience, work_address, work_scope, work_staff, work_phone, workdirector_name, Workindex, Workregion, Workcity, Workstreet, Workhousing, Workbuilding, Workroom, Workregion_shorttype, Workcity_shorttype, Workstreet_shorttype, income_base, income_additional, income_family, obligation, other_loan_month, other_loan_count, credit_history, other_max_amount, other_last_amount, bankrupt, education, marital_status, childs_count, have_car, has_estate, social_inst, social_fb, social_vk, social_ok, site_id, partner_id, partner_name, utm_source, utm_medium, utm_campaign, utm_content, utm_term, webmaster_id, click_hash, sms, tinkoff_id, UID, UID_status, rebillId, file_uploaded, need_remove, loan_history, fake_order_error, choose_insure, cdoctor_level, cdoctor_pdf, identified_phone, scorista_history_loaded, use_b2p, missing_manager_id, missing_status, missing_status_date, missing_real_date, sentData, files_checked, last_lk_visit_time, skip_credit_rating, date_skip_cr_visit, restructurisation, quantity_loans, blocked, timezone_id, call_status, continue_order, missing_manager_update_date, stage_in_contact, cdoctor_last_graph_update_date, cdoctor_last_graph_display_date, agree_claim_value, last_mark, generated_codes_count) VALUES (null, null, 1, 15000, 14, 1, 0, 1, 0, 1, 'test12345@test12345.com', '', '', 0, 1, null, 'test12345', '0000-00-00 00:00:00', 1, '0000-00-00 00:00:00', 0, null, 0, null, 0, null, 0, null, 0, null, 0, 'Тест', 'Тест', 'Тест', 'female', '14.12.1992', 'МОСКВА', '79123443210', null, null, '1234565433', '111-001', '08.05.2022', 'Москва', '', '', $registrationAddressId, $factualAddressId, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', null, '', '', null, '', null, null, '', '', '', '', '', '', '', '', '', '', '', null, null, null, '', '', '', '', '', '', '', '', '', '', '', 0, '', '', '', '', 'boostra', null, null, 'leads.su', 'Site', 'C1_main', '', '', '166694', 'ef3301184529ac6a235c5da3ff636350', '7842', null, '', '', '', 0, 0, null, 0, 0, 0, null, null, 0, 1, 45, 0, null, '2024-08-16 11:45:59', 0, 0, null, null, null, null, null, 0, null, 1, 1, '2024-08-13 16:37:05', 2, null, null, 0, null, 0);";

        $this->location->db->query($query);

        $this->userId = $this->location->db->insert_id();
    }
    private function addOrder(): void
    {
        $userId = $this->userId;

        $query = "INSERT INTO s_orders (contract_id, user_id, manager_id, cdoctor_id, accept_sms, accept_date, accept_try, manager_change_date, call_date, confirm_date, approve_date, reject_date, card_id, delivery_id, delivery_price, payment_method_id, paid, payment_date, closed, date, local_time, uid, name, address, phone, email, comment, status, url, payment_details, ip, total_price, note, discount, coupon_discount, coupon_code, separate_delivery, modified, amount, approve_amount, period, selected_period, percent, first_loan, sent_1c, sms, `1c_id`, `1c_status`, official_response, reason_id, crm_response, utm_source, utm_medium, utm_campaign, utm_content, utm_term, webmaster_id, click_hash, juicescore_session_id, scorista_sms_sent, have_close_credits, pay_result, razgon, max_amount, min_period, max_period, loan_type, payment_period, stage1, stage1_date, stage2, stage2_date, stage3, stage3_date, stage4, stage4_date, stage5, stage5_date, call_variants, leadgid_postback_date, credit_getted, b2p, autoretry, number_of_signing_errors, insurer, insure_amount, insure_percent, scorista_ball, is_credit_doctor, is_default_way, is_discount_way, payout_grade, leadgen_postback, send_user_info_date, order_uid, complete, promocode, is_user_credit_doctor, not_received_loan_manager_id, not_received_loan_manager_update_date, will_client_receive_loan, pti_loan, pti_order, pdn_notification_shown, additional_service, additional_service_repayment, additional_service_partial_repayment, deleteKD, organization_id, pdn_nkbi_loan, pdn_nkbi_order, cancellation_additional_services_by_phone) VALUES (0, $userId, null, null, null, null, 0, null, null, null, null, '0000-00-00 00:00:00', null, null, 0.00, null, 0, '0000-00-00 00:00:00', 0, '2017-08-07 03:02:58', null, null, '', '', '', '', '', 4, '33b3206fb830080da197bcbdad68bc59', '', '46.0.183.63', 0.00, '', 0.00, 0.00, '', 0, '2023-05-17 00:12:04', 5000, null, 10, null, 1, 0, 0, '', '', null, null, null, null, '', '', '', '', '', '', '', null, 1, 0, 'a:1:{s:6:\"return\";s:64:\"Статус заявки не равен одобренному\";}', 0, null, null, null, 'PDL', 1, 0, null, 0, null, 0, null, 0, null, 0, null, '', null, 0, 0, 0, 58, 'AL', 230, 0, 0, 0, null, null, null, null, null, '', null, null, 0, 0, null, null, null, null, 0, 1, 1, 1, null, 1, null, null, 0);";

        $this->location->db->query($query);

        $this->orderId = $this->location->db->insert_id();
    }

    private function addScoring(): void
    {
        $userId = $this->userId;
        $orderId = $this->orderId;

        $query = "INSERT INTO s_scorings (user_id, order_id, audit_id, type, status, success, created, scorista_id, scorista_status, scorista_ball, string_result, start_date, end_date, manual) VALUES ($userId, $orderId, 0, 20, 1, null, '2024-08-14 14:05:19', null, null, null, null, null, null, 0);";

        $this->location->db->query($query);

        $this->scoringId = $this->location->db->insert_id();
    }

    protected function tearDown(): void
    {
        $this->location->db->query('DELETE FROM users_addresses WHERE id = ' . $this->registrationAdressId);
        $this->location->db->query('DELETE FROM users_addresses WHERE id = ' . $this->factualAddressId);
        $this->location->db->query('DELETE FROM s_users WHERE id = ' . $this->userId);
        $this->location->db->query('DELETE FROM s_orders WHERE id = ' . $this->orderId);
        $this->location->db->query('DELETE FROM s_scorings WHERE id = ' . $this->scoringId);
    }
}
