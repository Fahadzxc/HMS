<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;
use App\Models\SettingModel;

class Settings extends Controller
{
    protected SettingModel $settings;

    public function __construct()
    {
        $this->settings = new SettingModel();
        $this->ensureTable();
    }

    public function index()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $defaults = [
            'hospital_name'            => 'MediCare Hospital',
            'hospital_email'           => 'admin@hospital.local',
            'hospital_phone'           => '+63 900 000 0000',
            'hospital_address'         => 'City, Country',
            'currency'                 => 'PHP',
            'timezone'                 => 'Asia/Manila',
            'logo_path'                => '',
            'module_appointments'      => '1',
            'module_laboratory'        => '1',
            'module_pharmacy'          => '1',
            'module_accounts'          => '1',
            'module_reports'           => '1',
            'appointments_overbook'    => '0',
            'appointments_window_days' => '30',
            'nurse_require_vitals'     => '1',
            'lab_auto_approve'         => '0',
            'pharmacy_low_stock'       => '10',
            'pharmacy_expiry_warn'     => '90',
            'billing_tax_rate'         => '0',
        ];

        $map = $this->settings->getAllAsMap();

        $data = [
            'title'     => 'Settings - HMS',
            'pageTitle' => 'Settings',
            'user_role' => 'admin',
            'user_name' => session()->get('name'),
            'settings'  => array_merge($defaults, $map),
        ];

        return view('admin/settings', $data);
    }

    public function save()
    {
        if (!session()->get('isLoggedIn') || session()->get('role') !== 'admin') {
            return redirect()->to('/login');
        }

        $post = $this->request->getPost();

        $groups = [
            'general'      => ['hospital_name','hospital_email','hospital_phone','hospital_address','currency','timezone'],
            'modules'      => ['module_appointments','module_laboratory','module_pharmacy','module_accounts','module_reports'],
            'appointments' => ['appointments_overbook','appointments_window_days'],
            'nurse'        => ['nurse_require_vitals'],
            'lab'          => ['lab_auto_approve'],
            'pharmacy'     => ['pharmacy_low_stock','pharmacy_expiry_warn'],
            'billing'      => ['billing_tax_rate'],
        ];

        foreach ($groups as $groupKey => $keys) {
            foreach ($keys as $key) {
                $value = (string) ($post[$key] ?? '');
                $this->settings->setValue($key, $value, $groupKey);
            }
        }

        $file = $this->request->getFile('logo_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = 'logo_' . time() . '.' . $file->getClientExtension();
            $target  = WRITEPATH . 'uploads';
            if (!is_dir($target)) {
                @mkdir($target, 0775, true);
            }
            if ($file->move($target, $newName)) {
                $rel = 'writable/uploads/' . $newName;
                $this->settings->setValue('logo_path', $rel, 'general');
            }
        }

        return redirect()->to('/admin/settings')->with('success', 'Settings saved successfully.');
    }

    protected function ensureTable(): void
    {
        $db = \Config\Database::connect();
        try {
            if ($db->tableExists('settings')) {
                return;
            }
        } catch (\Throwable $e) {
            // continue to create
        }

        try {
            $forge = \Config\Database::forge();
            $forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'key' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 191,
                ],
                'value' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'group_key' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'default'    => 'general',
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);
            $forge->addKey('id', true);
            $forge->addKey('key');
            $forge->createTable('settings', true);
        } catch (\Throwable $e) {
            // ignore creation errors
        }
    }
}


