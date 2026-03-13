<?php

namespace Tests\Unit;

use App\Modules\VoxCallsArchive\Application\DTO\VoxCallDTO;
use App\Modules\VoxCallsArchive\Application\Service\VoxCallsArchiveService;
use App\Modules\VoxCallsArchive\Infrastructure\Repository\VoxCallArchiveRepository;
use PHPUnit\Framework\TestCase;

class VoxCallsArchiveServiceTest extends TestCase
{
    public function testSaveFromLegacyMapsFieldsAndCallsRepository(): void
    {
        $call = new \stdClass();
        $call->call_cost = 12.5;
        $call->call_result_code = '200';
        $call->datetime_start = '2025-01-10 10:00:00';
        $call->duration = '42';
        $call->id = 555;
        $call->is_incoming = 1;
        $call->phone_a = '+79990001122';
        $call->phone_b = '+78880002233';
        $call->scenario_id = 9;
        $call->tags = [['tag_name' => 'test']];
        $call->created = '2025-01-10 10:05:00';
        $call->user_id = 777;
        $call->user_id_internal = 123;
        $call->queue_id = 33;
        $call->record_url = 'http://example.test/record';
        $call->assessment = '5';

        $expectedTags = json_encode($call->tags);

        $repository = $this->createMock(VoxCallArchiveRepository::class);
        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (VoxCallDTO $dto) use ($call, $expectedTags): bool {
                $this->assertSame(12.5, $dto->cost);
                $this->assertSame('200', $dto->callResultCode);
                $this->assertSame('2025-01-10 10:00:00', $dto->datetimeStart);
                $this->assertSame(42, $dto->duration);
                $this->assertSame(555, $dto->voxCallId);
                $this->assertTrue($dto->isIncoming);
                $this->assertSame('+79990001122', $dto->phoneA);
                $this->assertSame('+78880002233', $dto->phoneB);
                $this->assertSame(9, $dto->scenarioId);
                $this->assertSame($expectedTags, $dto->tags);
                $this->assertSame('2025-01-10 10:05:00', $dto->created);
                $this->assertSame(123, $dto->userId);
                $this->assertSame(33, $dto->queueId);
                $this->assertSame(777, $dto->voxUserId);
                $this->assertSame('http://example.test/record', $dto->recordUrl);
                $this->assertSame(5, $dto->assessment);
                return true;
            }))
            ->willReturn(99);

        $service = new VoxCallsArchiveService($repository);
        $result = $service->saveFromLegacy($call);

        $this->assertSame(99, $result);
    }

    public function testSaveFromArrayMapsFieldsAndCallsRepository(): void
    {
        $payload = [
            'cost' => 0,
            'call_result_code' => 'OK',
            'datetime_start' => '2025-02-01 12:00:00',
            'duration' => 15, // >= 10 seconds to pass filter
            'vox_call_id' => 999,
            'is_incoming' => false,
            'phone_a' => '+70000000001',
            'phone_b' => '+70000000002',
            'scenario_id' => 1,
            'tags' => '[]',
            'created' => '2025-02-01 12:00:01',
            'user_id' => 321,
            'queue_id' => 5,
            'vox_user_id' => 654,
            'record_url' => null,
            'assessment' => null,
        ];

        $repository = $this->createMock(VoxCallArchiveRepository::class);
        $repository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (VoxCallDTO $dto) use ($payload): bool {
                $this->assertSame(0, $dto->cost);
                $this->assertSame('OK', $dto->callResultCode);
                $this->assertSame('2025-02-01 12:00:00', $dto->datetimeStart);
                $this->assertSame(15, $dto->duration);
                $this->assertSame(999, $dto->voxCallId);
                $this->assertFalse($dto->isIncoming);
                $this->assertSame('+70000000001', $dto->phoneA);
                $this->assertSame('+70000000002', $dto->phoneB);
                $this->assertSame(1, $dto->scenarioId);
                $this->assertSame('[]', $dto->tags);
                $this->assertSame('2025-02-01 12:00:01', $dto->created);
                $this->assertSame(321, $dto->userId);
                $this->assertSame(5, $dto->queueId);
                $this->assertSame(654, $dto->voxUserId);
                $this->assertNull($dto->recordUrl);
                $this->assertNull($dto->assessment);
                return true;
            }))
            ->willReturn(7);

        $service = new VoxCallsArchiveService($repository);
        $result = $service->saveFromArray($payload);

        $this->assertSame(7, $result);
    }

    public function testSaveSkipsCallWithDurationLessThan10Seconds(): void
    {
        $payload = [
            'cost' => 0,
            'call_result_code' => 'BUSY',
            'datetime_start' => '2025-02-01 12:00:00',
            'duration' => 5, // < 10 seconds - should be skipped
            'vox_call_id' => 888,
            'is_incoming' => false,
            'phone_a' => '+70000000001',
            'phone_b' => '+70000000002',
        ];

        $repository = $this->createMock(VoxCallArchiveRepository::class);
        $repository->expects($this->never())
            ->method('save');

        $service = new VoxCallsArchiveService($repository);
        $result = $service->saveFromArray($payload);

        $this->assertNull($result);
    }

    public function testSaveSkipsCallWithZeroDuration(): void
    {
        $call = new \stdClass();
        $call->duration = 0;
        $call->id = 777;

        $repository = $this->createMock(VoxCallArchiveRepository::class);
        $repository->expects($this->never())
            ->method('save');

        $service = new VoxCallsArchiveService($repository);
        $result = $service->saveFromLegacy($call);

        $this->assertNull($result);
    }

    public function testSaveSkipsCallWithNullDuration(): void
    {
        $payload = [
            'vox_call_id' => 666,
            'duration' => null,
        ];

        $repository = $this->createMock(VoxCallArchiveRepository::class);
        $repository->expects($this->never())
            ->method('save');

        $service = new VoxCallsArchiveService($repository);
        $result = $service->saveFromArray($payload);

        $this->assertNull($result);
    }

    public function testSaveAcceptsCallWithExactly10Seconds(): void
    {
        $payload = [
            'duration' => 10, // exactly 10 seconds - should be saved
            'vox_call_id' => 555,
        ];

        $repository = $this->createMock(VoxCallArchiveRepository::class);
        $repository->expects($this->once())
            ->method('save')
            ->willReturn(100);

        $service = new VoxCallsArchiveService($repository);
        $result = $service->saveFromArray($payload);

        $this->assertSame(100, $result);
    }
}
