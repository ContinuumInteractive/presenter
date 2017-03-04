<?php

namespace DBonner\Depot\Presentation;

trait PresentableModel
{
    /**
     * @inheritdoc
     */
    public function getPresentableKeys()
    {
        // Use the Eloquent Models `getAttributes` to return the keys.
        return array_keys($this->getAttributes());
    }
}
