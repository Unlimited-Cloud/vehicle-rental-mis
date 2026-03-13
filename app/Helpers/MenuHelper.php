<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use App\Models\Module;

class MenuHelper
{
    /**
     * Check if menu or any of its children is active
     *
     * @param array $item
     * @return bool
     */
    public static function isActive(array $item): bool
    {
        // Single route item
        if (isset($item['route']) && $item['route'] && Route::currentRouteName() === $item['route']) {
            return true;
        }

        // Treeview menu
        if (isset($item['children']) && is_array($item['children'])) {
            foreach ($item['children'] as $child) {
                if (self::isActive($child)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if menu or children has permission
     *
     * @param array $item
     * @return bool
     */
    public static function hasPermission(array $item): bool
    {
        if (isset($item['permission']) && $item['permission'] && Gate::allows($item['permission'])) {
            return true;
        }

        if (isset($item['children']) && is_array($item['children'])) {
            foreach ($item['children'] as $child) {
                if (self::hasPermission($child)) {
                    return true;
                }
            }
        }

        return false;
    }

    public static function getParentModules(){
        return Module::whereNull('parent_id')
        ->orderBy('order_by')
        ->get()
        ->toArray();
    }

    public static function getSubModulesByParentId($parentId){
        return Module::where('parent_id',$parentId)->orderBy('order_by')->get()->toArray();
    }
}
