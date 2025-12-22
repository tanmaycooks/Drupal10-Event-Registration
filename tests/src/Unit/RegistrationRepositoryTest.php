<?php

namespace Drupal\Tests\event_registration\Unit;

use Drupal\Tests\UnitTestCase;
use Drupal\event_registration\Repository\RegistrationRepository;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\Query\QueryInterface;

/**
 * Tests the RegistrationRepository.
 *
 * @group event_registration
 */
class RegistrationRepositoryTest extends UnitTestCase
{

    protected $repository;
    protected $entityTypeManager;
    protected $storage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
        $this->storage = $this->createMock(EntityStorageInterface::class);
        $this->entityTypeManager->method('getStorage')->willReturn($this->storage);

        $this->repository = new RegistrationRepository($this->entityTypeManager);
    }

    public function testCountRegistrations()
    {
        $query = $this->createMock(QueryInterface::class);
        $query->method('accessCheck')->willReturn($query);
        $query->method('condition')->willReturn($query);
        $query->method('count')->willReturn($query);
        $query->method('execute')->willReturn(5);

        $this->storage->method('getQuery')->willReturn($query);

        $this->assertEquals(5, $this->repository->countRegistrations(1));
    }

}
