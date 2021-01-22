# FastlyWatermark

php bin/magento module:enable Andrewdaluz_Fastly

Add Custom VCL to work with the solution (name as recv_5_overlay_watermark.vcl):
```
if (req.url.qs ~ "overlay") {
    set req.http.X-fastly-imageopto-overlay = "overlay=/media/catalog/product/watermark/"+ subfield(req.url.qs, "overlay", "&") +"&overlay-height=0.80&overlay-width=0.80";
}
```

