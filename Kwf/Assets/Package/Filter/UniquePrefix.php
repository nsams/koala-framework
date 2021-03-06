<?php
class Kwf_Assets_Package_Filter_UniquePrefix
{
    public static function filter($map, $uniquePrefix)
    {
        $head = '
        (function() {
            if (!window.'.$uniquePrefix.') window.'.$uniquePrefix.' = {};
            var kwfUp = window.'.$uniquePrefix.';

            var kwfNamespaces = ["Kwf", "Kwc", "Ext2", "$", "jQuery", "Modernizr", "require", "trl", "trlp"];

            var kwfOrigExports = {};
            for (var i=0; i<kwfNamespaces.length; i++) {
                var name = kwfNamespaces[i];
                kwfOrigExports[name] = window[name];
                if (kwfUp[name]) {
                    window[name] = kwfUp[name];
                } else {
                    try {
                        delete window[name];
                    } catch (e) {
                        window[name] = undefined;
                    }
                }
            }
            if (!window.Ext2) window.Ext2 = {};
        ';

        $foot = '
        for (var i=0; i<kwfNamespaces.length; i++) {
                var name = kwfNamespaces[i];
                kwfUp[name] = window[name] || eval(name);
                if (kwfOrigExports[name]) {
                    window[name] = kwfOrigExports[name];
                } else {
                    try {
                        delete window[name];
                    } catch (e) {
                        window[name] = undefined;
                    }
                }
                eval("var "+name+" = kwfUp."+name+";");
            }
        })();
        ';

        $ret = Kwf_SourceMaps_SourceMap::createEmptyMap('');
        $ret->concat(Kwf_SourceMaps_SourceMap::createEmptyMap($head));
        $ret->concat($map);
        $ret->concat(Kwf_SourceMaps_SourceMap::createEmptyMap($foot));

        return $ret;
    }
}

