<?php
class SettingController extends Controller {
    public function index() {
        // Only admin can access setting module
        Auth::requireRole(['admin']);
        
        $settingModel = new Setting();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'order_online' => $_POST['order_online'] ?? 'nonaktif',
                'inkaso_online' => $_POST['inkaso_online'] ?? 'nonaktif'
            ];
            
            // Save or create setting
            $settingModel->saveOrCreate($data);
            Message::success('Setting berhasil disimpan');
            $this->redirect('/setting');
        }
        
        // Get current setting (may be null if empty)
        $setting = $settingModel->getMainSetting();
        
        // Provide default values if setting is empty
        if (!$setting) {
            $setting = [
                'id' => null,
                'order_online' => 'nonaktif',
                'inkaso_online' => 'nonaktif'
            ];
        }
        
        $data = [
            'setting' => $setting
        ];
        
        $this->view('settings/index', $data);
    }
}
