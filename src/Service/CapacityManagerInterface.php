<?php

namespace Drupal\event_registration\Service;

/**
 * Interface for capacity manager.
 */
interface CapacityManagerInterface
{

    /**
     * Check if an event has space.
     *
     * @param \Drupal\node\NodeInterface $event
     *   The event node.
     *
     * @return bool
     *   TRUE if space is available, FALSE otherwise.
     */
    public function hasSpace($event);

    /**
     * Get remaining spots.
     *
     * @param \Drupal\node\NodeInterface $event
     *   The event node.
     *
     * @return int
     *   Number of remaining spots.
     */
    public function getRemainingSpots($event);

}
