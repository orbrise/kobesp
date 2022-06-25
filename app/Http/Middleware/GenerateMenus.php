<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Str;

class GenerateMenus
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \Menu::make('admin_sidebar', function ($menu) {
            // Dashboard
            $menu->add('<i class="cil-speedometer c-sidebar-nav-icon"></i> Dashboard', [
                'route' => 'backend.dashboard',
                'class' => 'c-sidebar-nav-item',
            ])
            ->data([
                'order'         => 1,
                'activematches' => 'admin/dashboard*',
            ])
            ->link->attr([
                'class' => 'c-sidebar-nav-link',
            ]);

            // Notifications
           

            // Separator: Access Management
            $menu->add('Management', [
                'class' => 'c-sidebar-nav-title',
            ])
            ->data([
                'order'         => 100,
                'permission'    => ['edit_settings', 'view_backups', 'view_users', 'view_roles', 'view_logs'],
            ]);


            // Products
			
			$menu->add('<i class="cil-bank c-sidebar-nav-icon"></i> States', [
                'route' => 'backend.states',
                'class' => 'c-sidebar-nav-item',
            ])
            ->data([
                'order'         => 99,
                'activematches' => 'admin/states*',
            ])
            ->link->attr([
                'class' => 'c-sidebar-nav-link',
            ]);
			
			
            $menu->add('<i class="cil-folder-open c-sidebar-nav-icon"></i> Categories', [
                'route' => 'backend.allcategories',
                'class' => 'c-sidebar-nav-item',
            ])
            ->data([
                'order'         => 101,
                'activematches' => 'admin/allcategories*',
            ])
            ->link->attr([
                'class' => 'c-sidebar-nav-link',
            ]);


				
            $menu->add('<i class="cil-school c-sidebar-nav-icon"></i> Schools', [
                'route' => 'backend.schools',
                'class' => 'c-sidebar-nav-item',
            ])
            ->data([
                'order'         => 102,
                'activematches' => 'admin/schools*',
            ])
            ->link->attr([
                'class' => 'c-sidebar-nav-link',
            ]);
			
			/*$menu->add('<i class="cil-boat-alt c-sidebar-nav-icon"></i> Shipping Table', [
                'route' => 'backend.shipping',
                'class' => 'c-sidebar-nav-item',
            ])
            ->data([
                'order'         => 102,
                'activematches' => 'admin/shipping*',
            ])
            ->link->attr([
                'class' => 'c-sidebar-nav-link',
            ]);*/
			
			$menu->add('<i class="cil-clipboard c-sidebar-nav-icon"></i> Orders', [
                'route' => 'backend.orderslist',
                'class' => 'c-sidebar-nav-item',
            ])
            ->data([
                'order'         => 102,
                'activematches' => 'admin/orders-list*',
            ])
            ->link->attr([
                'class' => 'c-sidebar-nav-link',
            ]);
			
			$menu->add('<i class="cil-group c-sidebar-nav-icon"></i> Pending User ', [
                'route' => 'backend.pendingusers',
                'class' => 'c-sidebar-nav-item',
            ])
            ->data([
                'order'         => 102,
                'activematches' => 'admin/pending-request*',
            ])
            ->link->attr([
                'class' => 'c-sidebar-nav-link',
            ]);
			
			
			$reports = $menu->add('<i class="c-sidebar-nav-icon cil-copy"></i> Reports', [
                'class' => 'c-sidebar-nav-dropdown',
            ])
            ->data([
                'order'         => 103,
                'activematches' => [
                    'admin/stock-report*',
                    'admin/sale-report*',
                ],
            ]);
            $reports->link->attr([
                'class' => 'c-sidebar-nav-dropdown-toggle',
				
            ]);
			
			// Submenu: Users
            $reports->add('<i class="c-sidebar-nav-icon cil-3d"></i> Stock Report', [
                'class' => 'nav-item',
            ])
            ->data([
                'order'         => 111,
                'activematches' => '/admin/stock-report*'
            ])
            ->link->attr([
                'class' => 'c-sidebar-nav-link',
				'href'  => '/admin/stock-report',
            ]);
			
			$reports->add('<i class="c-sidebar-nav-icon cil-bar-chart"></i> Sale Report', [
                'class' => 'nav-item',
            ])
            ->data([
                'order'         => 111,
                'activematches' => '/admin/sale-report*'
            ])
            ->link->attr([
                'class' => 'c-sidebar-nav-link',
				'href'  => '/admin/sale-report',
            ]);
			
			$reports->add('<i class="c-sidebar-nav-icon cil-bar-chart"></i> Saleman Report', [
                'class' => 'nav-item',
            ])
            ->data([
                'order'         => 111,
                'activematches' => '/admin/saleman-report*'
            ])
            ->link->attr([
                'class' => 'c-sidebar-nav-link',
				'href'  => '/admin/saleman-report',
            ]);
			
			
			
			/*$reports->add('<i class="c-sidebar-nav-icon cil-wallet"></i> Commission Report', [
                'class' => 'nav-item',
            ])
            ->data([
                'order'         => 112,
                'activematches' => '/admin/comission*',
				'permission'    => ['view_commission'],
            ])
            ->link->attr([
                'class' => 'c-sidebar-nav-link',
				'href'  => '/admin/comission',
            ]);*/
			
			
			
            

            // Backup
            
            // Access Control Dropdown
         


   $menu->add('<i class="c-sidebar-nav-icon fas fa-cogs"></i> Settings', [
                'route' => 'backend.settings',
                'class' => 'c-sidebar-nav-item',
            ])
            ->data([
                'order'         => 110,
                'activematches' => 'admin/settings*',
            ])
            ->link->attr([
                'class' => 'c-sidebar-nav-link',
            ]);

$menu->add('<i class="cil-user c-sidebar-nav-icon"></i> Users', [
                
                'class' => 'c-sidebar-nav-item',
            ])
            ->data([
                'order'         => 112,
                'activematches' => 'admin/allusers*',
            ])
            ->link->attr([
                'class' => 'c-sidebar-nav-link',
				'href'  => '/admin/allusers',
            ]);
 
            // Submenu: Users
         

            // Submenu: Roles
            /*$accessControl->add('<i class="c-sidebar-nav-icon cil-people"></i> Roles', [
                'route' => 'backend.roles.index',
                'class' => 'nav-item',
            ])
            ->data([
                'order'         => 112,
                'activematches' => 'admin/roles*',
                'permission'    => ['view_roles'],
            ])
            ->link->attr([
                'class' => 'c-sidebar-nav-link',
            ]);*/

            // Log Viewer
            // Log Viewer Dropdown
          

            // Submenu: Log Viewer Dashboard
           

            // Submenu: Log Viewer Logs by Days
          

            // Access Permission Check
            $menu->filter(function ($item) {
                if ($item->data('permission')) {
                    if (auth()->check()) {
                        if (auth()->user()->hasRole('super admin')) {
                            return true;
                        } elseif (auth()->user()->hasAnyPermission($item->data('permission'))) {
                            return true;
                        }
                    }

                    return false;
                } else {
                    return true;
                }
            });

            // Set Active Menu
            $menu->filter(function ($item) {
                if ($item->activematches) {
                    $matches = is_array($item->activematches) ? $item->activematches : [$item->activematches];

                    foreach ($matches as $pattern) {
                        if (Str::is($pattern, \Request::path())) {
                            $item->activate();
                            $item->active();
                            if ($item->hasParent()) {
                                $item->parent()->activate();
                                $item->parent()->active();
                            }
                            // dd($pattern);
                        }
                    }
                }

                return true;
            });
        })->sortBy('order');

        return $next($request);
    }
}
