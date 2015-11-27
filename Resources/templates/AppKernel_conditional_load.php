    private function registerBundleIfExists($bundle, &$bundles)
    {
        if (class_exists($bundle)) {
            array_push($bundles, new $bundle());
        }
    }
    
    public function registerBundles()