<?php
use Illuminate\Support\Str;

return [

  /*
  |--------------------------------------------------------------------------
  | Title
  |--------------------------------------------------------------------------
  |
  | The default title of your admin panel, this goes into the title tag
  | of your page. You can override it per page with the title section.
  | You can optionally also specify a title prefix and/or postfix.
  |
  */

  'title' => 'Demo ERP',

  'title_prefix' => '',

  'title_postfix' => '',

  /*
  |--------------------------------------------------------------------------
  | Logo
  |--------------------------------------------------------------------------
  |
  | This logo is displayed at the upper left corner of your admin panel.
  | You can use basic HTML here if you want. The logo has also a mini
  | variant, used for the mini side bar. Make it 3 letters or so
  |
  */

  'logo' => '<img src="'. env('APP_URL').'img/logo_baru.jpeg' .'" height="35" alt="demoerp">',

  'logo_mini' => '<img src="'. env('APP_URL').'img/logo_baru.jpeg' .'" height="35" alt="demoerp">',

  /*
  |--------------------------------------------------------------------------
  | Skin Color
  |--------------------------------------------------------------------------
  |
  | Choose a skin color for your admin panel. The available skin colors:
  | blue, black, purple, yellow, red, and green. Each skin also has a
  | ligth variant: blue-light, purple-light, purple-light, etc.
  |
  */

  'skin' => 'red-light',

  /*
  |--------------------------------------------------------------------------
  | Layout
  |--------------------------------------------------------------------------
  |
  | Choose a layout for your admin panel. The available layout options:
  | null, 'boxed', 'fixed', 'top-nav'. null is the default, top-nav
  | removes the sidebar and places your menu in the top navbar
  |
  */

  'layout' => null,

  /*
  |--------------------------------------------------------------------------
  | Collapse Sidebar
  |--------------------------------------------------------------------------
  |
  | Here we choose and option to be able to start with a collapsed side
  | bar. To adjust your sidebar layout simply set this  either true
  | this is compatible with layouts except top-nav layout option
  |
  */

  'collapse_sidebar' => false,

  /*
  |--------------------------------------------------------------------------
  | URLs
  |--------------------------------------------------------------------------
  |
  | Register here your dashboard, logout, login and register URLs. The
  | logout URL automatically sends a POST request in Laravel 5.3 or higher.
  | You can set the request to a GET or POST with logout_method.
  | Set register_url to null if you don't want a register link.
  |
  */

  'dashboard_url' => '/',

  'logout_url' => 'logout',

  'logout_method' => null,

  'login_url' => 'login',

  'register_url' => 'register',

  /*
  |--------------------------------------------------------------------------
  | Menu Items
  |--------------------------------------------------------------------------
  |
  | Specify your menu items to display in the left sidebar. Each menu item
  | should have a text and and a URL. You can also specify an icon from
  | Font Awesome. A string instead of an array represents a header in sidebar
  | layout. The 'can' is a filter on Laravel's built in Gate functionality.
  |
  */

  'menu' => [
    // 'PENJUALAN',
    // [
    //   'text'        => 'Pages',
    //   'url'         => 'admin/pages',
    //   'icon'        => 'file',
    //   'label'       => 4,
    //   'label_color' => 'success',
    //   'icon_color' => 'red',
    // ],
    // [
    //   'text' => 'Penawaran Penjualan',
    //   'url'  => 'sales/quotation',
    //   'model' => 'sales/quotation',
    //   'can' => 'access-menu',
    //   'icon' => 'handshake-o',
    // ],
    // [
    //   'text' => 'Order Penjualan',
    //   'url'  => 'sales/order',
    //   'model'  => 'sales/order',
    //   'can' => 'access-menu',
    //   'icon' => 'shopping-cart',
    // ],
    // [
    //   'text' => 'Instruksi Pengiriman',
    //   'url'  => 'sales/shipping-instruction',
    //   'model'  => 'sales/shipping-instruction',
    //   'can' => 'access-menu',
    //   'icon' => 'external-link-square',
    // ],
    // [
    //   'text' => 'Nota Pengiriman',
    //   'url'  => 'sales/delivery-note',
    //   'model'  => 'sales/delivery-note',
    //   'can' => 'access-menu',
    //   'icon' => 'truck',
    // ],
    // [
    //   'text' => 'Faktur Penjualan',
    //   'url'  => 'sales/invoice',
    //   'model'  => 'sales/invoice',
    //   'can' => 'access-menu',
    //   'icon' => 'file-text',
    // ],
    'PEMBELIAN',
    [
      'text' => 'Purchase Order',
      'url'  => 'purchase/request',
      'model'  => 'purchase/request',
      'can' => 'access-menu',
    'icon' => 'shopping-basket',
    ],
    [
      'text' => 'Surat Perintah Kerja',
      'url'  => 'production/job-order',
      'model'  => 'production/job-order',
      'can' => 'access-menu',
      'icon' => 'suitcase',
    ],
    [
      'text' => 'Berita Acara',
      'url'  => 'sales/delivery-note',
      'model'  => 'sales/delivery-note',
      'can' => 'access-menu',
      'icon' => 'truck',
    ],
    [
      'text' => 'Penerimaan Barang',
      'url'  => 'purchase/receipt',
      'model'  => 'purchase/receipt',
      'can' => 'access-menu',
      'icon' => 'file-text-o',
    ],
    [
      'text' => 'Pengiriman Barang',
      'url'  => 'sales/shipping-instruction',
      'model'  => 'sales/shipping-instruction',
      'can' => 'access-menu',
      'icon' => 'truck',
    ],
    'MASTER',
    [
      'text' => 'Cabang',
      'url'  => 'master/branch',
      'model'  => 'master/branch',
      'can' => 'access-menu',
      'icon' => 'puzzle-piece',
    ],
    [
      'text' => 'Unit',
      'url'  => 'master/unit',
      'model'  => 'master/unit',
      'can' => 'access-menu',
      'icon' => 'puzzle-piece',
    ],
    [
      'text'    => 'Kota/Kabupaten',
      'model'    => Str::slug('City', '_'),
      'can' => 'access-menu',
      'icon'    => 'puzzle-piece',
      'submenu' => [
        [
          'text' => 'Provinsi',
          'url'  => 'master/city/province',
          'model'  => 'master/city/province',
          'can' => 'access-menu',
        ],
        [
          'text' => 'Kota/Kabupaten',
          'url'  => 'master/city/city',
          'model'  => 'master/city/city',
          'can' => 'access-menu',
        ],
        // [
        //     'text' => 'Disctrict',
        //     'url'  => 'master/city/district',
        //     'model'  => 'master/city/district',
        //     // 'can' => 'access-menu',
        // ],
      ],
    ],
    
    [
      'text'    => 'Item',
      'model'    => Str::slug('Item', '_'),
      'can' => 'access-menu',
      'icon'    => 'cubes',
      'submenu' => [
        [
          'text' => 'Kategory Item',
          'url'  => 'master/item/category',
          'model'  => 'master/item/category',
          'can' => 'access-menu',
          'icon' => 'sitemap'
        ],
        [
          'text' => 'Item',
          'url'  => 'master/item/item',
          'model'  => 'master/item/item',
          'can' => 'access-menu',
          'icon' => 'cube'
        ]
      ],
    ],
    [
      'text'    => 'Pembayaran',
      'model'    => Str::slug('Payment', '_'),
      'can' => 'access-menu',
      'icon'    => 'credit-card',
      'submenu' => [
        [
          'text' => 'Metode Pembayaran',
          'url'  => 'master/payment/method',
          'model'  => 'master/payment/method',
          'can' => 'access-menu',
        ],
        [
          'text' => 'Pilihan Bank Pembayaran',
          'url'  => 'master/payment/bank-channel',
          'model'  => 'master/payment/bank-channel',
          'can' => 'access-menu',
        ]
      ],
    ],
    [
      'text'    => 'Pengiriman',
      'model'    => Str::slug('Shipping', '_'),
      'can' => 'access-menu',
      'icon'    => 'truck',
      'submenu' => [
        [
          'text' => 'Biaya Pengiriman',
          'url'  => 'master/shipping/cost',
          'model'  => 'master/shipping/cost',
          'can' => 'access-menu',
        ]
      ],
    ],
    [
      'text' => 'Vendor',
      'model' => Str::slug('Vendor', '_'),
      'can' => 'access-menu',
      'icon' => 'address-book',
      'submenu' => [
        [
          'text' => 'Perusahaan',
          'url'  => 'master/customer/company',
          'model'  => 'master/customer/company',
          'can' => 'access-menu',
          'icon' => 'building'
        ],
        [
          'text' => 'Vendor',
          'url'  => 'master/customer',
          'model'  => 'master/customer',
          'can' => 'access-menu',
          'icon' => 'address-book'
        ],
      ]
    ],
    [
      'text'    => 'Karyawan',
      'model'    => Str::slug('Employee', '_'),
      'can' => 'access-menu',
      'icon'    => 'address-card-o',
      'submenu' => [
        [
          'text' => 'Peran Karyawan',
          'url'  => 'master/employee/role',
          'model'  => 'master/employee/role',
          'can' => 'access-menu',
          'icon' => 'key',
        ],
        [
          'text' => 'Karyawan',
          'url'  => 'master/employee',
          'model'  => 'master/employee',
          'can' => 'access-menu',
          'icon' => 'user-o',
        ]
      ],
    ],
  ],

  /*
  |--------------------------------------------------------------------------
  | Menu Filters
  |--------------------------------------------------------------------------
  |
  | Choose what filters you want to include for rendering the menu.
  | You can add your own filters to this array after you've created them.
  | You can comment out the GateFilter if you don't want to use Laravel's
  | built in Gate functionality
  |
  */

  'filters' => [
    JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
    JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
    // JeroenNoten\LaravelAdminLte\Menu\Filters\SubmenuFilter::class,
    JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
    JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
  ],

  /*
  |--------------------------------------------------------------------------
  | Plugins Initialization
  |--------------------------------------------------------------------------
  |
  | Choose which JavaScript plugins should be included. At this moment,
  | only DataTables is supported as a plugin. Set the value to true
  | to include the JavaScript file from a CDN via a script tag.
  |
  */

  'plugins' => [
    'datatables' => true,
    'select2'    => true,
    'chartjs'    => true,
  ],
];
