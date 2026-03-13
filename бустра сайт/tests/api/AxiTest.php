<?php

namespace api;

use PHPUnit\Framework\TestCase;

require_once __DIR__ . "/../../api/Axi.php";
require_once __DIR__ . "/../../api/Scorings.php";
require_once __DIR__ . "/../../api/OpenSearchLogger.php";
require_once __DIR__ . "/../../api/Orders.php";
require_once __DIR__ . "/../../api/UserData.php";
require_once __DIR__ . "/../../api/Database.php";
require_once __DIR__ . "/../../api/OrderData.php";

/**
 * Тесты для класса Axi
 *
 * Тестирует методы для определения типа флоу (с/без КИ),
 * чтение данных из s_axilink / order_data и синхронизацию BKI consent
 */
class AxiTest extends TestCase
{
    /** @var \Axi|\PHPUnit\Framework\MockObject\MockObject */
    private $axi;

    /** @var \Scorings|\PHPUnit\Framework\MockObject\MockObject */
    private $scoringsMock;

    /** @var \OpenSearchLogger|\PHPUnit\Framework\MockObject\MockObject */
    private $openSearchLoggerMock;

    /** @var \Orders|\PHPUnit\Framework\MockObject\MockObject */
    private $ordersMock;

    /** @var \UserData|\PHPUnit\Framework\MockObject\MockObject */
    private $userDataMock;

    /** @var \Database|\PHPUnit\Framework\MockObject\MockObject */
    private $dbMock;

    /** @var \OrderData|\PHPUnit\Framework\MockObject\MockObject */
    private $orderDataMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->scoringsMock = $this->createMock(\Scorings::class);
        $this->openSearchLoggerMock = $this->createMock(\OpenSearchLogger::class);
        $this->ordersMock = $this->createMock(\Orders::class);
        $this->userDataMock = $this->createMock(\UserData::class);
        $this->dbMock = $this->createMock(\Database::class);
        $this->orderDataMock = $this->createMock(\OrderData::class);

        $this->axi = $this->getMockBuilder(\Axi::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();

        $this->axi->method('__get')
            ->willReturnCallback(function ($name) {
                $map = [
                    'scorings' => $this->scoringsMock,
                    'open_search_logger' => $this->openSearchLoggerMock,
                    'orders' => $this->ordersMock,
                    'user_data' => $this->userDataMock,
                    'db' => $this->dbMock,
                    'order_data' => $this->orderDataMock,
                ];
                return $map[$name] ?? null;
            });
    }

    private function makeScoring(int $orderId, string $body, int $status = \Scorings::STATUS_COMPLETED): object
    {
        return (object) [
            'id' => 1,
            'order_id' => $orderId,
            'type' => \Scorings::TYPE_AXILINK_2,
            'status' => $status,
            'body' => $body,
        ];
    }

    private function setLastScoringReturn(?object $scoring): void
    {
        $this->scoringsMock
            ->method('getLastScoring')
            ->willReturn($scoring);
    }

    /**
     * Настраивает db mock для возврата XML из s_axilink
     */
    private function setupDbForAxilink(?string $xml): void
    {
        $this->dbMock->method('placehold')->willReturn('SELECT ...');
        $this->dbMock->method('query')->willReturn(true);
        $this->dbMock->method('result')->willReturn($xml);
    }

    // =========================================================================
    // getLastNbkiRequestByOrderId
    // =========================================================================

    public function testGetLastNbkiRequestByOrderId_UsesStatusCompleted(): void
    {
        $orderId = 123;
        $scoring = $this->makeScoring($orderId, '<response/>');

        $this->scoringsMock
            ->expects($this->once())
            ->method('getLastScoring')
            ->with($this->callback(function (array $where) use ($orderId) {
                return $where['order_id'] === $orderId
                    && $where['type'] === \Scorings::TYPE_AXILINK_2
                    && $where['status'] === \Scorings::STATUS_COMPLETED;
            }))
            ->willReturn($scoring);

        $result = $this->axi->getLastNbkiRequestByOrderId($orderId);

        $this->assertSame($scoring, $result);
    }

    public function testGetLastNbkiRequestByOrderId_WhenNotFound_ReturnsNull(): void
    {
        $this->setLastScoringReturn(null);

        $result = $this->axi->getLastNbkiRequestByOrderId(123);

        $this->assertNull($result);
    }

    // =========================================================================
    // getLastNbkiRequestByUserId
    // =========================================================================

    public function testGetLastNbkiRequestByUserId_WhenZeroId_ReturnsNull(): void
    {
        $this->assertNull($this->axi->getLastNbkiRequestByUserId(0));
    }

    public function testGetLastNbkiRequestByUserId_WhenFound_ReturnsObject(): void
    {
        $row = (object) ['created_at' => '2024-06-01 12:00:00'];

        $this->dbMock->method('placehold')->willReturn('SELECT ...');
        $this->dbMock->method('query')->willReturn(true);
        $this->dbMock->method('result')->willReturn($row);

        $result = $this->axi->getLastNbkiRequestByUserId(456);

        $this->assertSame($row, $result);
    }

    public function testGetLastNbkiRequestByUserId_WhenNotFound_ReturnsNull(): void
    {
        $this->dbMock->method('placehold')->willReturn('SELECT ...');
        $this->dbMock->method('query')->willReturn(true);
        $this->dbMock->method('result')->willReturn(null);

        $this->assertNull($this->axi->getLastNbkiRequestByUserId(456));
    }

    // =========================================================================
    // getAxilinkAllowSimplifiedFlow (читает из s_axilink)
    // =========================================================================

    public function testGetAxilinkAllowSimplifiedFlow_WhenZeroOrderId_ReturnsNull(): void
    {
        $this->assertNull($this->axi->getAxilinkAllowSimplifiedFlow(0));
    }

    public function testGetAxilinkAllowSimplifiedFlow_WhenNoXml_ReturnsNull(): void
    {
        $this->setupDbForAxilink(null);

        $this->assertNull($this->axi->getAxilinkAllowSimplifiedFlow(123));
    }

    public function testGetAxilinkAllowSimplifiedFlow_WhenInvalidXml_ReturnsNull(): void
    {
        $this->setupDbForAxilink('not valid xml <<<');

        $this->assertNull($this->axi->getAxilinkAllowSimplifiedFlow(123));
    }

    public function testGetAxilinkAllowSimplifiedFlow_WhenTrue_ReturnsTrue(): void
    {
        $xml = '<Application><AXI><application_e allow_simplified_flow="true"/></AXI></Application>';
        $this->setupDbForAxilink($xml);

        $this->assertTrue($this->axi->getAxilinkAllowSimplifiedFlow(123));
    }

    public function testGetAxilinkAllowSimplifiedFlow_WhenTrueUppercase_ReturnsTrue(): void
    {
        $xml = '<Application><AXI><application_e allow_simplified_flow="True"/></AXI></Application>';
        $this->setupDbForAxilink($xml);

        $this->assertTrue($this->axi->getAxilinkAllowSimplifiedFlow(123));
    }

    public function testGetAxilinkAllowSimplifiedFlow_WhenFalse_ReturnsFalse(): void
    {
        $xml = '<Application><AXI><application_e allow_simplified_flow="false"/></AXI></Application>';
        $this->setupDbForAxilink($xml);

        $this->assertFalse($this->axi->getAxilinkAllowSimplifiedFlow(123));
    }

    public function testGetAxilinkAllowSimplifiedFlow_WhenAttributeMissing_ReturnsNull(): void
    {
        $xml = '<Application><AXI><application_e dss_name="test"/></AXI></Application>';
        $this->setupDbForAxilink($xml);

        $this->assertNull($this->axi->getAxilinkAllowSimplifiedFlow(123));
    }

    public function testGetAxilinkAllowSimplifiedFlow_WhenNoApplicationE_ReturnsNull(): void
    {
        $xml = '<Application><AXI><other_node/></AXI></Application>';
        $this->setupDbForAxilink($xml);

        $this->assertNull($this->axi->getAxilinkAllowSimplifiedFlow(123));
    }

    // =========================================================================
    // getOrderOrgSwitchResult (читает из order_data)
    // =========================================================================

    public function testGetOrderOrgSwitchResult_ReturnsValue(): void
    {
        $this->orderDataMock
            ->expects($this->once())
            ->method('read')
            ->with(123, 'order_org_switch_result')
            ->willReturn('SUCCESS_WITH_ORGANIZATION_SWITCH_2');

        $this->assertSame(
            'SUCCESS_WITH_ORGANIZATION_SWITCH_2',
            $this->axi->getOrderOrgSwitchResult(123)
        );
    }

    public function testGetOrderOrgSwitchResult_WhenNotFound_ReturnsNull(): void
    {
        $this->orderDataMock
            ->method('read')
            ->willReturn(null);

        $this->assertNull($this->axi->getOrderOrgSwitchResult(123));
    }

    // =========================================================================
    // getParentOrderOrgSwitchResult (читает из order_data)
    // =========================================================================

    public function testGetParentOrderOrgSwitchResult_UsesParentOrder(): void
    {
        $this->orderDataMock
            ->method('read')
            ->willReturnCallback(function (int $orderId, string $key) {
                if ($orderId === 123 && $key === 'order_org_switch_parent_order_id') {
                    return '555';
                }
                if ($orderId === 555 && $key === 'order_org_switch_result') {
                    return 'SUCCESS_WITH_ORGANIZATION_SWITCH_2';
                }
                return null;
            });

        $this->assertSame(
            'SUCCESS_WITH_ORGANIZATION_SWITCH_2',
            $this->axi->getParentOrderOrgSwitchResult(123)
        );
    }

    public function testGetParentOrderOrgSwitchResult_WhenParentIdMissing_ReturnsNull(): void
    {
        $this->orderDataMock
            ->method('read')
            ->willReturn(null);

        $this->assertNull($this->axi->getParentOrderOrgSwitchResult(123));
    }

    public function testGetParentOrderOrgSwitchResult_WhenParentIdNotNumeric_ReturnsNull(): void
    {
        $this->orderDataMock
            ->method('read')
            ->willReturnCallback(function (int $orderId, string $key) {
                if ($key === 'order_org_switch_parent_order_id') {
                    return 'abc';
                }
                return null;
            });

        $this->assertNull($this->axi->getParentOrderOrgSwitchResult(123));
    }

    // =========================================================================
    // isOrderWithKiRequest
    // =========================================================================

    public function testIsOrderWithKiRequest_WhenZeroOrderId_ReturnsFalse(): void
    {
        $this->assertFalse($this->axi->isOrderWithKiRequest(0));
    }

    public function testIsOrderWithKiRequest_WhenAxilinkFlagFalse_AndOrgSwitchWithKi_ReturnsTrue(): void
    {
        // allow_simplified_flow="false" — axilinkFlag === false
        $xml = '<Application><AXI><application_e allow_simplified_flow="false"/></AXI></Application>';
        $this->setupDbForAxilink($xml);

        $this->orderDataMock
            ->method('read')
            ->willReturnCallback(function (int $orderId, string $key) {
                if ($key === 'order_org_switch_result') {
                    return 'SUCCESS_WITH_ORGANIZATION_SWITCH_2';
                }
                return null;
            });

        $this->assertTrue($this->axi->isOrderWithKiRequest(123));
    }

    public function testIsOrderWithKiRequest_WhenAxilinkFlagTrue_ReturnsFalse(): void
    {
        $xml = '<Application><AXI><application_e allow_simplified_flow="true"/></AXI></Application>';
        $this->setupDbForAxilink($xml);

        $this->assertFalse($this->axi->isOrderWithKiRequest(123));
    }

    public function testIsOrderWithKiRequest_WhenAxilinkFlagNull_ReturnsFalse(): void
    {
        // Нет записи в s_axilink → allow_simplified_flow неизвестен. В order_data нет признаков КИ → false.
        $this->setupDbForAxilink(null);

        $this->assertFalse($this->axi->isOrderWithKiRequest(123));
    }

    public function testIsOrderWithKiRequest_WhenParentOrgSwitchWithKi_ReturnsTrue(): void
    {
        $xml = '<Application><AXI><application_e allow_simplified_flow="false"/></AXI></Application>';
        $this->setupDbForAxilink($xml);

        $this->orderDataMock
            ->method('read')
            ->willReturnCallback(function (int $orderId, string $key) {
                if ($orderId === 123 && $key === 'order_org_switch_result') {
                    return null;
                }
                if ($orderId === 123 && $key === 'order_org_switch_parent_order_id') {
                    return '555';
                }
                if ($orderId === 555 && $key === 'order_org_switch_result') {
                    return 'SUCCESS_WITH_ORGANIZATION_SWITCH_2';
                }
                return null;
            });

        $this->assertTrue($this->axi->isOrderWithKiRequest(123));
    }

    // =========================================================================
    // isSimplifiedFlowOrder
    // =========================================================================

    public function testIsSimplifiedFlowOrder_WhenZeroOrderId_ReturnsFalse(): void
    {
        $this->assertFalse($this->axi->isSimplifiedFlowOrder(0));
    }

    public function testIsSimplifiedFlowOrder_WhenAxilinkFlagTrue_ReturnsTrue(): void
    {
        $xml = '<Application><AXI><application_e allow_simplified_flow="true"/></AXI></Application>';
        $this->setupDbForAxilink($xml);

        $this->assertTrue($this->axi->isSimplifiedFlowOrder(123));
    }

    public function testIsSimplifiedFlowOrder_WhenOrgSwitchWithoutKi_ReturnsTrue(): void
    {
        $this->setupDbForAxilink(null);

        $this->orderDataMock
            ->method('read')
            ->willReturnCallback(function (int $orderId, string $key) {
                if ($key === 'order_org_switch_result') {
                    return 'SUCCESS_WITH_ORGANIZATION_SWITCH_3';
                }
                return null;
            });

        $this->assertTrue($this->axi->isSimplifiedFlowOrder(123));
    }

    public function testIsSimplifiedFlowOrder_WhenParentOrgSwitchWithoutKi_ReturnsTrue(): void
    {
        $this->setupDbForAxilink(null);

        $this->orderDataMock
            ->method('read')
            ->willReturnCallback(function (int $orderId, string $key) {
                if ($orderId === 123 && $key === 'order_org_switch_result') {
                    return null;
                }
                if ($orderId === 123 && $key === 'order_org_switch_parent_order_id') {
                    return '555';
                }
                if ($orderId === 555 && $key === 'order_org_switch_result') {
                    return 'SUCCESS_WITH_ORGANIZATION_SWITCH_3';
                }
                return null;
            });

        $this->assertTrue($this->axi->isSimplifiedFlowOrder(123));
    }

    public function testIsSimplifiedFlowOrder_WhenNeitherConditionMet_ReturnsFalse(): void
    {
        $xml = '<Application><AXI><application_e allow_simplified_flow="false"/></AXI></Application>';
        $this->setupDbForAxilink($xml);

        $this->orderDataMock
            ->method('read')
            ->willReturn(null);

        $this->assertFalse($this->axi->isSimplifiedFlowOrder(123));
    }

    // =========================================================================
    // syncBkiConsent
    // =========================================================================

    public function testSyncBkiConsent_WhenConsentAlreadyTrue_ReturnsTrue(): void
    {
        $this->userDataMock
            ->expects($this->once())
            ->method('read')
            ->with(456, \UserData::BKI_CONSENT)
            ->willReturn('{"consent":true}');

        $this->userDataMock
            ->expects($this->never())
            ->method('set');

        $this->assertTrue($this->axi->syncBkiConsent(456));
    }

    public function testSyncBkiConsent_WhenConsentFalse_DoesNotSkip(): void
    {
        $order = (object) ['id' => 123];

        $this->userDataMock
            ->method('read')
            ->willReturn('{"consent":false}');

        $this->ordersMock
            ->method('get_last_order')
            ->willReturn($order);

        // simplified flow → записываем consent=false
        $xml = '<Application><AXI><application_e allow_simplified_flow="true"/></AXI></Application>';
        $this->setupDbForAxilink($xml);

        $this->userDataMock
            ->expects($this->once())
            ->method('set')
            ->with(
                456,
                \UserData::BKI_CONSENT,
                $this->callback(function ($json) {
                    $data = json_decode($json, true);
                    return $data['consent'] === false;
                })
            );

        $this->assertTrue($this->axi->syncBkiConsent(456));
    }

    public function testSyncBkiConsent_WhenNoOrder_ReturnsFalse(): void
    {
        $this->userDataMock
            ->method('read')
            ->willReturn(null);

        $this->ordersMock
            ->expects($this->once())
            ->method('get_last_order')
            ->with(456)
            ->willReturn(null);

        $this->userDataMock
            ->expects($this->never())
            ->method('set');

        $this->assertFalse($this->axi->syncBkiConsent(456));
    }

    public function testSyncBkiConsent_WhenSimplifiedFlow_SetsConsentFalse(): void
    {
        $order = (object) ['id' => 123];

        $this->userDataMock
            ->method('read')
            ->willReturn(null);

        $this->ordersMock
            ->method('get_last_order')
            ->willReturn($order);

        // allow_simplified_flow="true" → isSimplifiedFlowOrder=true
        $xml = '<Application><AXI><application_e allow_simplified_flow="true"/></AXI></Application>';
        $this->setupDbForAxilink($xml);

        $this->userDataMock
            ->expects($this->once())
            ->method('set')
            ->with(
                456,
                \UserData::BKI_CONSENT,
                $this->callback(function ($json) {
                    $data = json_decode($json, true);
                    return $data['consent'] === false
                        && $data['source'] === 'axi_sync'
                        && $data['order_id'] === 123;
                })
            );

        $this->assertTrue($this->axi->syncBkiConsent(456));
    }

    public function testSyncBkiConsent_WhenOrderWithKi_SetsConsentTrue(): void
    {
        $order = (object) ['id' => 123];

        $this->userDataMock
            ->method('read')
            ->willReturn(null);

        $this->ordersMock
            ->method('get_last_order')
            ->willReturn($order);

        // allow_simplified_flow="false" → isSimplifiedFlowOrder=false, isOrderWithKiRequest проверяет orgSwitch
        $xml = '<Application><AXI><application_e allow_simplified_flow="false"/></AXI></Application>';
        $this->setupDbForAxilink($xml);

        $this->orderDataMock
            ->method('read')
            ->willReturnCallback(function (int $orderId, string $key) {
                if ($key === 'order_org_switch_result') {
                    return 'SUCCESS_WITH_ORGANIZATION_SWITCH_2';
                }
                return null;
            });

        $this->userDataMock
            ->expects($this->once())
            ->method('set')
            ->with(
                456,
                \UserData::BKI_CONSENT,
                $this->callback(function ($json) {
                    $data = json_decode($json, true);
                    return $data['consent'] === true
                        && $data['source'] === 'axi_sync'
                        && $data['order_id'] === 123;
                })
            );

        $this->assertTrue($this->axi->syncBkiConsent(456));
    }

    public function testSyncBkiConsent_FallbackNbkiLog_SetsConsentTrue(): void
    {
        $order = (object) ['id' => 123];
        $nbkiLog = (object) ['created_at' => '2024-06-01 12:00:00'];

        $this->userDataMock
            ->method('read')
            ->willReturn(null);

        $this->ordersMock
            ->method('get_last_order')
            ->willReturn($order);

        // Нет s_axilink записи → isSimplified=false, isOrderWithKi: axilinkFlag=null, null !== false → return false
        $this->dbMock->method('placehold')->willReturn('SELECT ...');
        $this->dbMock->method('query')->willReturn(true);
        // Первый вызов result → null (s_axilink для isSimplifiedFlowOrder)
        // Второй вызов result → null (s_axilink для isOrderWithKiRequest)
        // Третий вызов result → $nbkiLog (getLastNbkiRequestByUserId)
        $this->dbMock->method('result')
            ->willReturnOnConsecutiveCalls(null, null, $nbkiLog);

        $this->orderDataMock
            ->method('read')
            ->willReturn(null);

        $this->userDataMock
            ->expects($this->once())
            ->method('set')
            ->with(
                456,
                \UserData::BKI_CONSENT,
                $this->callback(function ($json) {
                    $data = json_decode($json, true);
                    return $data['consent'] === true
                        && $data['timestamp'] === '2024-06-01 12:00:00';
                })
            );

        $this->assertTrue($this->axi->syncBkiConsent(456));
    }

    public function testSyncBkiConsent_FallbackCompletedScoring_SetsConsentFalse(): void
    {
        $order = (object) ['id' => 123];
        $scoring = (object) [
            'status' => \Scorings::STATUS_COMPLETED,
            'completed' => '2024-05-01 08:00:00',
        ];

        $this->userDataMock
            ->method('read')
            ->willReturn(null);

        $this->ordersMock
            ->method('get_last_order')
            ->willReturn($order);

        // Нет s_axilink, нет NBKI лога
        $this->dbMock->method('placehold')->willReturn('SELECT ...');
        $this->dbMock->method('query')->willReturn(true);
        $this->dbMock->method('result')
            ->willReturn(null);

        $this->orderDataMock
            ->method('read')
            ->willReturn(null);

        $this->scoringsMock
            ->method('getLastScoring')
            ->willReturn($scoring);

        $this->userDataMock
            ->expects($this->once())
            ->method('set')
            ->with(
                456,
                \UserData::BKI_CONSENT,
                $this->callback(function ($json) {
                    $data = json_decode($json, true);
                    return $data['consent'] === false
                        && $data['timestamp'] === '2024-05-01 08:00:00';
                })
            );

        $this->assertTrue($this->axi->syncBkiConsent(456));
    }

    public function testSyncBkiConsent_WhenNothingMatches_ReturnsFalse(): void
    {
        $order = (object) ['id' => 123];

        $this->userDataMock
            ->method('read')
            ->willReturn(null);

        $this->ordersMock
            ->method('get_last_order')
            ->willReturn($order);

        $this->dbMock->method('placehold')->willReturn('SELECT ...');
        $this->dbMock->method('query')->willReturn(true);
        $this->dbMock->method('result')->willReturn(null);

        $this->orderDataMock
            ->method('read')
            ->willReturn(null);

        $this->setLastScoringReturn(null);

        $this->userDataMock
            ->expects($this->never())
            ->method('set');

        $this->assertFalse($this->axi->syncBkiConsent(456));
    }

    public function testSyncBkiConsent_WhenExceptionThrown_LogsErrorAndReturnsFalse(): void
    {
        $this->userDataMock
            ->method('read')
            ->willThrowException(new \Exception('Database error'));

        $this->openSearchLoggerMock
            ->expects($this->once())
            ->method('create')
            ->with(
                'Error syncing BKI consent',
                $this->callback(function (array $context) {
                    return ($context['user_id'] ?? null) === 456
                        && strpos($context['error'] ?? '', 'Database error') !== false;
                }),
                'bki_consent_sync',
                \OpenSearchLogger::LOG_LEVEL_ERROR
            );

        $this->assertFalse($this->axi->syncBkiConsent(456));
    }
}
