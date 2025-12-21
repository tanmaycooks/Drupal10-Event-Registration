<?php

namespace Drupal\event_registration\Entity;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Registration entities.
 */
class RegistrationViewsData extends EntityViewsData
{

    /**
     * {@inheritdoc}
     */
    public function getViewsData()
    {
        // Get the default data from the entity type.
        $data = parent::getViewsData();

        $data['event_registration']['table']['base'] = [
            'field' => 'id',
            'title' => $this->t('Event Registration'),
            'help' => $this->t('The Registration entity ID.'),
        ];

        return $data;
    }

}
