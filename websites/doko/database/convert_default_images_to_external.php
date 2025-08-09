<?php
/**
 * convert_default_images_to_external.php
 *
 * Replaces default / missing product images with external placeholder CDN links.
 * Generates URLs of the form:
 *   https://via.placeholder.com/600x450.png?text=Slugified+Product+Name
 * and stores them in product_images table as the new primary image.
 *
 * Usage:
 *   php convert_default_images_to_external.php --dry-run   (show what would change)
 *   php convert_default_images_to_external.php --apply      (perform updates)
 *   php convert_default_images_to_external.php --apply --force-replace-all  (replace even non-default images)
 *
 * Default detection: image_url NULL / empty OR contains 'default-product' OR ends with '.svg'
 */

require_once __DIR__ . '/../../config/database.php';

$opts = getopt('', [
    'dry-run',
    'apply',
    'force-replace-all'
]);
$dry = isset($opts['dry-run']) && !isset($opts['apply']);
$apply = isset($opts['apply']);
$forceAll = isset($opts['force-replace-all']);

function out($m){ echo $m . PHP_EOL; }

try {
    $db = Database::getInstance()->getConnection();

    $products = $db->query("SELECT product_id, name FROM products ORDER BY product_id")->fetchAll(PDO::FETCH_ASSOC);
    if(!$products){ out('No products found.'); exit; }

    $selImg = $db->prepare('SELECT image_id, image_url, is_primary FROM product_images WHERE product_id=? ORDER BY is_primary DESC, image_id ASC');
    $demote = $db->prepare('UPDATE product_images SET is_primary=0 WHERE product_id=?');
    $insert = $db->prepare('INSERT INTO product_images (product_id, image_url, alt_text, is_primary, sort_order) VALUES (?,?,?,?,0)');
    $delete = $db->prepare('DELETE FROM product_images WHERE image_id=?');

    $changed = 0; $skipped = 0; $replaced = 0;

    foreach($products as $p){
        $pid = (int)$p['product_id'];
        $name = trim($p['name'] ?: ('Product '.$pid));
        $slugTxt = preg_replace('~[^a-z0-9]+~i','+', strtolower($name));
        $slugTxt = trim($slugTxt,'+');
        if($slugTxt==='') $slugTxt = 'product';
        $externalUrl = 'https://via.placeholder.com/600x450.png?text=' . urlencode($slugTxt);

        $selImg->execute([$pid]);
        $imgs = $selImg->fetchAll(PDO::FETCH_ASSOC);

        $needsReplace = $forceAll; // start with force flag
        $hasPrimary = false; $defaultPrimaryId = null; $nonDefaultPrimary = null;
        foreach($imgs as $img){
            if($img['is_primary']){
                $hasPrimary = true;
                $url = $img['image_url'];
                $isDefaultLike = ($url === null || $url === '' || stripos($url,'default-product') !== false || preg_match('~\.svg($|\?)~i',$url));
                if($isDefaultLike){ $needsReplace = true; $defaultPrimaryId = (int)$img['image_id']; }
                else { $nonDefaultPrimary = $img; }
            }
        }

        if(!$hasPrimary) $needsReplace = true; // no primary at all

        if(!$needsReplace){ $skipped++; continue; }

        if($dry){
            out("[DRY] Product #$pid '$name' -> assign external primary: $externalUrl" . ($defaultPrimaryId?" (replacing default image_id $defaultPrimaryId)":""));
            $changed++;
            continue;
        }

        $db->beginTransaction();
        try {
            if($defaultPrimaryId){
                // remove old default record to keep table clean
                $delete->execute([$defaultPrimaryId]);
            } else {
                // demote existing primary if force mode replacing a non-default
                if($nonDefaultPrimary && $forceAll){
                    $demote->execute([$pid]);
                    $replaced++;
                } else {
                    $demote->execute([$pid]);
                }
            }
            $insert->execute([$pid, $externalUrl, $name, 1]);
            $db->commit();
            $changed++;
            out("Updated product #$pid -> primary image set to external placeholder");
        } catch(Exception $ie){
            $db->rollBack();
            out("ERROR updating product #$pid: ".$ie->getMessage());
        }
    }

    out(str_repeat('-',50));
    out("Summary: changed=$changed skipped=$skipped replaced_non_default=$replaced mode=" . ($dry?'DRY':'APPLY'));

} catch (Exception $e) {
    out('Fatal error: '.$e->getMessage());
    exit(1);
}
