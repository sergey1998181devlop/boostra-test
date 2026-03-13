<?php

use Phinx\Migration\AbstractMigration;

class AddAxiConfigToOrganizationsData extends AbstractMigration
{
    public function up(): void
    {
        $this->execute("
            INSERT INTO s_organizations_data (organization_id, `key`, value) VALUES
            (11, 'axi_config', '{\"base\": {\"service_ip\": \"158.160.27.231\", \"version\": \"axilink-1.0\", \"create_action\": \"CreateApplication\", \"command\": \"START\", \"ProductCode\": \"Scorista_pdl_dvlp\", \"ProductCategory\": \"Scorista_dvlp_pdl\", \"dss_name\": \"FICO_4_10_v2\", \"AxilinkProductCode\": \"pdl_boostra2_short\", \"AxilinkProductCategory\": \"boostra2_short_pdl\"}, \"cross\": {\"service_ip\": \"51.250.104.144\", \"version\": \"axilink-1.0\", \"create_action\": \"SyncApplication\", \"command\": \"NBKI\", \"ProductCode\": \"Scorista_pdl_dvlp\", \"ProductCategory\": \"Scorista_dvlp_pdl\", \"dss_name\": \"FICO_4_10_v2\", \"AxilinkProductCode\": \"pdl_boostra2_short\", \"AxilinkProductCategory\": \"boostra2_short_pdl\"}}'),
            (13, 'axi_config', '{\"base\": {\"service_ip\": \"158.160.12.6\", \"version\": \"axilink-1.0\", \"create_action\": \"CreateApplication\", \"command\": \"START\", \"ProductCode\": \"Scorista_pdl_dvlp\", \"ProductCategory\": \"Scorista_dvlp_pdl\", \"dss_name\": \"FICO_4_10_v2\", \"AxilinkProductCode\": \"pdl_boostra2_short\", \"AxilinkProductCategory\": \"boostra2_short_pdl\"}}'),
            (14, 'axi_config', '{\"base\": {\"service_ip\": \"89.169.163.111\", \"version\": \"axilink-1.0\", \"create_action\": \"CreateApplication\", \"command\": \"START\", \"ProductCode\": \"Scorista_pdl_dvlp\", \"ProductCategory\": \"Scorista_dvlp_pdl\", \"dss_name\": \"FICO_4_10\", \"AxilinkProductCode\": \"pdl_boostra2_short\", \"AxilinkProductCategory\": \"boostra2_short_pdl\"}}'),
            (17, 'axi_config', '{\"base\": {\"service_ip\": \"89.169.172.63\", \"version\": \"axilink-1.0\", \"create_action\": \"CreateApplication\", \"command\": \"START\", \"ProductCode\": \"Scorista_pdl_dvlp\", \"ProductCategory\": \"Scorista_dvlp_pdl\", \"dss_name\": \"FICO_4_10_v2\", \"AxilinkProductCode\": \"pdl_boostra2_short\", \"AxilinkProductCategory\": \"boostra2_short_pdl\"}}'),
            (20, 'axi_config', '{\"base\": {\"service_ip\": \"51.250.28.209\", \"version\": \"axilink-1.0\", \"create_action\": \"CreateApplication\", \"command\": \"START\", \"ProductCode\": \"frida_dvlp\", \"ProductCategory\": \"frida_pdl\", \"dss_name\": \"FICO_4_10_v2\", \"AxilinkProductCode\": \"frida_short_dvlp\", \"AxilinkProductCategory\": \"frida_short_pdl\"}, \"cross\": {\"service_ip\": \"51.250.28.209\", \"version\": \"axilink-1.0\", \"create_action\": \"SyncApplication\", \"command\": \"NBKI\", \"ProductCode\": \"frida_dvlp\", \"ProductCategory\": \"frida_pdl\", \"dss_name\": \"FICO_4_10_v2\", \"AxilinkProductCode\": \"frida_short_dvlp\", \"AxilinkProductCategory\": \"frida_short_pdl\"}}')
        ");
    }

    public function down(): void
    {
        $this->execute("DELETE FROM s_organizations_data WHERE `key` = 'axi_config'");
    }
}
