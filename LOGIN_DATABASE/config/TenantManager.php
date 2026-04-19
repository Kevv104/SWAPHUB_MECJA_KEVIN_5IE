<?php
#classe Tenant Manager
#gestisce il tenant corrente da sessione PHP
#la classe ha funzioni dedicate per accesso dati

class TenantManager 
{
  public static function get_current_tenant_id() #ritorna il tenant id della sessione
  {
     return $_SESSION['tenant_id'] ?? null;
  }

  public static function set_current_tenant_id($tenant_id) #imposta tenant in sessione
  {
    $_SESSION['tenant_id'] = $tenant_id;
  }

   public static function get_current_tenant() { //ritorna info del current tenant
        $tenant_id = self::get_current_tenant_id();
        if (!$tenant_id) return null;
        
        $config = require __DIR__ . '/tenants_config.php';
        return $config['tenants'][$tenant_id] ?? null;
    }

    public static function get_all_tenants() { //ci restituisce lista di tutti i tenant
        $config = require __DIR__ . '/tenants_config.php';
        return $config['tenants'];
    }

    public static function validate_tenant_id($tenant_id) { //valida la validità di un tenant
        $config = require __DIR__ . '/tenants_config.php';
        return isset($config['tenants'][$tenant_id]);
    }

     public static function get_tenant_id_by_name($name) { //per ottenere il tenant_id dal nome del tenant
        $config = require __DIR__ . '/tenants_config.php';
        foreach ($config['tenants'] as $id => $tenant) {
            if ($tenant['name'] === $name) {
                return $id;
            }
        }
        return null;
    }
}
?>