<?php

namespace Pvtl\VoyagerPageBlocks;

use TCG\Voyager\Traits\Resizable;
use Pvtl\VoyagerFrontend\Helpers\ClassEvents;

class Page extends \Pvtl\VoyagerFrontend\Page
{
    use Resizable;

    // Add relation to page blocks
    public function blocks()
    {
        return $this->hasMany('Pvtl\VoyagerPageBlocks\PageBlock');
    }

    /**
     * Get the indexed data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();

        // Include page block data to be "Searchable"
        $pageBlocks = $this->blocks()->get()->map(function ($block) {
            // If it's an included file, return the HTML of this block to be searched
            if ($block->type === 'include') {
                return trim(preg_replace(
                    '/\s+/',
                    ' ',
                    strip_tags(ClassEvents::executeClass($block->path)->render())
                ));
            }

            $blockContent = [];

            foreach ($block->data as $datum) {
                $blockContent[] = strip_tags($datum);
            }

            return $blockContent;
        });

        $array['page_blocks'] = implode(' ', array_flatten($pageBlocks));

        return $array;
    }
}
