/*
 * NovaeZMenuManagerBundle.
 *
 * @package   NovaeZMenuManagerBundle
 *
 * @author    florian
 * @copyright 2019 Novactive
 * @license   https://github.com/Novactive/NovaeZMenuManagerBundle/blob/master/LICENSE
 *
 */

const path = require('path')

module.exports = (eZConfig, eZConfigManager) => {
  eZConfigManager.add({
    eZConfig,
    entryName: 'ezplatform-admin-ui-content-edit-parts-css',
    newItems: [
      path.resolve(__dirname, '../public/scss/fieldType/edit/syllabs.scss')
    ]
  })
  eZConfigManager.add({
    eZConfig,
    entryName: 'ezplatform-admin-ui-content-edit-parts-js',
    newItems: [
      path.resolve(__dirname, '../public/js/module/syllabs.suggester.module.js'),
      path.resolve(__dirname, '../public/js/fieldType/syllabs.js')
    ]
  })
}
