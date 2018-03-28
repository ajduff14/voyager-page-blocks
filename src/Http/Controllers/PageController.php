<?php

namespace Pvtl\VoyagerPageBlocks\Http\Controllers;

use Pvtl\VoyagerPageBlocks\Page;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\View;
use Pvtl\VoyagerPageBlocks\Traits\Blocks;

class PageController extends Controller
{
    use Blocks;

    /**
     * Fetch all pages and their associated blocks
     *
     * @param string $slug
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getPage($slug = 'home')
    {
        $page = Page::where('slug', '=', $slug)->firstOrFail();
        $blocks = $page->blocks()
            ->where('is_hidden', '=', '0')
            ->orderBy('order', 'asc')
            ->get()
            ->map(function ($block) {
                return (object)[
                    'id' => $block->id,
                    'page_id' => $block->page_id,
                    'updated_at' => $block->updated_at,
                    'cache_ttl' => $block->cache_ttl,
                    'template' => $block->template()->template,
                    'data' => $block->cachedData,
                    'path' => $block->path,
                    'type' => $block->type,
                ];
            });

        // Override standard body content, with page block content
        $page['body'] = view('voyager-page-blocks::default', [
            'page' => $page,
            'blocks' => $this->prepareEachBlock($blocks, $page),
        ]);

        // Check that the page Layout and its View exists
        if (empty($page->layout)) {
            $page->layout = 'default';
        }
        if (!View::exists('voyager-frontend::layouts.' . $page->layout)) {
            $page->layout = 'default';
        }

        // Return the full page
        return view('voyager-frontend::modules.pages.default', [
            'page' => $page,
            'layout' => $page->layout,
        ]);
    }
}
