(function ($, Drupal) {
  Drupal.behaviors.pagedesigner_init_responsive_images = {
    attach: function (context, settings) {

      $(document).on('pagedesigner-before-setup', function (e, editor) {
        window.TwigFunctions = {};
        window.TwigFunctions.json_decode = function(value){
          return JSON.parse(value);
        }

        Twig.extendFunction('image_style', function (sizes, template) {
          if (sizes && template) {
            let imageStyles = {}
            Object.keys(drupalSettings.pagedesigner_responsive_images.image_style_templates[template].settings).forEach(function(breakpoint){
              imageStyles[breakpoint] = {
                'templates' : drupalSettings.pagedesigner_responsive_images.image_style_templates[template].settings[breakpoint],
                'size' : JSON.parse(sizes)[breakpoint]
              }
            });
            return imageStyles;
          }
          return [];
        });

        Twig.extendFunction('create_attribute', function (attributes) {
          let attr = '';
          attributes._keys.forEach(function(key){
            attr += key + '="'+ attributes[key] + '" ';
          })
          return attr;
        });

        Twig.extendFunction('styled_image_url', function (uri, imageStyle) {
          return uri.replace('public://sites/default/files', '/sites/default/files').replace('/files/', '/files/styles/' + imageStyle + '/public/');
        });

        Twig.extendFunction('file_url', function (uri) {
          return uri.replace('public:/', '')
        });
      });

      $(document).on('pagedesigner-init-blocks', function (e, editor) {
        editor.DomComponents.responsiveComponentTypes =  editor.BlockManager.getAll().filter(function(block){
          return block.attributes.additional.responsive_images
        }).map(function(block){
          return block.get('id')
        });

        editor.DomComponents.responsiveComponentTypes.forEach(function(type){

          let sizesField = editor.BlockManager.get(type).attributes.additional.responsive_images.component_sizes_field;

          editor.DomComponents.addType(type, {
            extend: type,
            model: {
              beforeSave() {
                this.set(sizesField, JSON.stringify(this.calculateSizes()));
                this.attributes.attributes[sizesField] = JSON.stringify(this.calculateSizes());
                this.set('changed', false);
              },
              calculateSizes: function () {
                let $el = $(this.view.el);
                let $parents = $el.parentsUntil('[data-gjs-type="container"]', Object.keys(drupalSettings.pagedesigner_responsive_images.sizes).join(',')).toArray();
                let sizes = {};
                $parents.reverse().forEach(function (parent) {
                  for (let selector of Object.keys(drupalSettings.pagedesigner_responsive_images.sizes)) {
                    if ($(parent).is(selector)) {
                      for (let size of Object.keys(drupalSettings.pagedesigner_responsive_images.sizes[selector])) {
                        if (sizes[size]) {
                          if (typeof drupalSettings.pagedesigner_responsive_images.sizes[selector][size] == 'number') {
                            sizes[size] += ' * ' + drupalSettings.pagedesigner_responsive_images.sizes[selector][size];
                          } else {
                            sizes[size] = ' min( ' + sizes[size] + ' , ' + drupalSettings.pagedesigner_responsive_images.sizes[selector][size] + ' ) ';
                          }
                        } else {
                          sizes[size] = drupalSettings.pagedesigner_responsive_images.sizes[selector][size];
                        }
                      }
                      break
                    }
                  }
                });

                Object.keys(sizes).map(function (key, index) {
                  sizes[key] = 'calc(' + sizes[key] + ')';
                });

                return sizes;
              },
            }
          });
        })
      });

      // extend image trait
      $(document).on('pagedesigner-init-base-components', function (e, editor) {

        editor.DomComponents.getChildren = (model, result = []) => {
          result.push(model);
          model.components().each(mod => editor.DomComponents.getChildren(mod, result))
          return result;
        }

        editor.DomComponents.addType('row', {
          extend: 'row',
          model: {
            afterSave() {
              editor.DomComponents.getChildren(this).filter(function(cmp){
                return editor.DomComponents.responsiveComponentTypes.includes(cmp.get('type'))
              }).forEach(function(cmp){
                setTimeout(function(){
                  cmp.save();
                }, 100);
              });
            },
            afterLoad() {
              editor.runCommand('edit-component');
              this.get('traits').models.forEach(function (trait) {
                if (trait.view && trait.view.afterInit) {
                  trait.view.afterInit();
                }
              });
              editor.Panels.getPanel('spinner-loading').set('visible', false);
              editor.DomComponents.getChildren(this).filter(function(cmp){
                return editor.DomComponents.responsiveComponentTypes.includes(cmp.get('type'))
              }).forEach(function(component){
                if (!isNaN(parseFloat(component.get('entityId'))) && isFinite(component.get('entityId'))) {
                  Drupal.restconsumer.get('/pagedesigner/element/' + component.get('entityId')).done(function (response) {
                    component.changed = false;
                    component.attributes.attributes = Object.assign({}, component.getAttributes(), response['fields']);
                  });
                }
              });
            },
          }
        });

        const TraitManager = editor.TraitManager;

        // add image_style_template field
        TraitManager.addType('image_style_template', Object.assign({}, TraitManager.defaultTrait, {
          events: {
            change: 'onChange',
          },

          afterInit: function () {
            var value = Object.keys(this.options)[0];
            if (typeof this.model.attributes.additional.preview !== 'undefined' && this.model.attributes.additional.preview) {
              value = this.model.attributes.additional.preview;
            }
            if (this.model.get('value') && this.model.get('value')[0] && Object.keys(this.options).indexOf(this.model.get('value')[0]) > -1) {
              value = this.model.get('value')[0];
            } else {
              this.model.set('value', [value]);
              editor.getSelected().set('changed', false);
            }
            $(this.inputEl).find('option[value="' + value + '"]').attr('selected', 'selected');

            // move template field to image trait
            let templateFields = patterns[this.target.attributes.type].additional.responsive_images.template_fields;
            let imageField = Object.keys(templateFields).find(key => templateFields[key] === this.model.get('name'));
            $(this.inputEl).closest('.gjs-trt-trait').insertBefore(this.target.getTrait(imageField).view.$el.find('.gjs-trait-meta'))
          },

          getInputEl: function () {
            if (!this.inputEl) {
              this.options = Object.assign({'': {'label':  Drupal.t('Auswählen')}}, drupalSettings.pagedesigner_responsive_images.image_style_templates);
              var select = $('<select>');
              for (var key in this.options) {
                var option = $('<option value="' + key + '">' + this.options[key].label + '</option>');
                select.append(option);
              }
              this.inputEl = select.get(0);
            }
            return this.inputEl;
          },
          getRenderValue: function (value) {
            if (typeof this.model.get('value') == 'undefined') {
              return value;
            }
            return this.model.get('value');
          },
          setTargetValue: function (value) {
            this.model.set('value', value);
          },
          setInputValue: function (value) {
            if (value) {
              this.model.set('value', value);
              $(this.inputEl).val(value);
            }
          },
          onValueChange(model, value) {
            var opts = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
            if (opts.fromTarget) {
              value = this.model.get('value');
              this.model.renderValue = this.getRenderValue(value);
              this.setInputValue(value);
            } else {
              var value = this.getValueForTarget();
              this.model.renderValue = this.getRenderValue(value);
              this.model.target.attributes.attributes[this.model.get('name')] = [value];
              component = this.model.target;
              component.updateView(component, { force: true });
            }

            for (var targetField in this.model.attributes.relations ) {
              var sourceKey = this.model.attributes.relations[targetField].source_key;
              var overrideTarget = this.model.attributes.relations[targetField].override;
              var targetTrait = this.target.getTrait(targetField);
              if( sourceKey && value ){
                var sourceValue = value[sourceKey];
              }else{
                var sourceValue = value;
              }
              var targetKey = this.model.attributes.relations[targetField].target_key;
              if( targetKey ){
                var targetValue = targetTrait.getTargetValue() || {};
                if( overrideTarget || ( !overrideTarget && !targetValue[targetKey] ) ){
                  targetValue[targetKey] = sourceValue;
                  targetTrait.setTargetValue({});
                  targetTrait.setTargetValue( targetValue );
                }
              }else{
                if( overrideTarget || ( !overrideTarget && !targetTrait.getTargetValue() ) ){
                  targetTrait.setTargetValue( sourceValue );
                }
              }
            }

            this.addMetaData();
            let templateFields = patterns[this.target.attributes.type].additional.responsive_images.template_fields;
            let imageField = Object.keys(templateFields).find(key => templateFields[key] === this.model.get('name'));
            this.target.getTrait(imageField).renderValue = this.target.getTrait(imageField).view.getRenderValue()
          },
        })
        );

        // add component sizes field
        TraitManager.addType('component_sizes', Object.assign({}, TraitManager.defaultTrait, {
          events: {
            change: 'onChange', // trigger parent onChange method on keyup
          },
          getInputEl: function () {
            let value = JSON.stringify(this.model.target.calculateSizes());
            this.model.setTargetValue(value);
            this.model.target.attributes.attributes[this.model.get('name')] = value;
            if (!this.inputEl) {
              var input = $('<input type="hidden" />');
              input.val(value);
              this.inputEl = input.get(0);
            }
            return this.inputEl;
          },

          afterInit: function () {
            $(this.el).hide();
          },

        }));

        // extend image field
        editor.PDMediaManager.addTrait('image', 'file', {
          getMetaData: function getMetaData() {
            if (!this.$metaHolder.hasClass('btn-remove')) {
              var trait = this;
              this.$metaHolder.attr('title', Drupal.t('Remove image'));
              this.$metaHolder.click(function () {
                if (confirm(Drupal.t('Remove image from component?'))) {
                  trait.model.set('value', { id: null });
                  trait.getMetaData()
                }
              });
              this.$metaHolder.addClass('btn-remove');
            }

            if (this.model.get('value') && this.model.get('value').src) {
              return '<img src="' + this.model.get('value').src + '"/>';
            }
            return '';
          },
          getRenderValue: function () {
            let value = this.model.get('value');

            if (!value || !value.id) {
              return '';
            }

            return 'public:/' + value.src.replace(location.origin, '');
          },
        });


        AssetManager = editor.AssetManager;
        var galleryTrait = null;

        TraitManager.addType('gallery',
        Object.assign({}, TraitManager.defaultTrait, {
          events: {
            change: 'onChange',  // trigger parent onChange method on keyup
          },
          getInputEl: function () {
            if (!this.inputEl) {
              galleryTrait = new GalleryTrait(settings);
              this.inputEl = galleryTrait.getContainer(this);
              var value = this.model.get('value');
              if (value && value.id && value.name) {
                galleryTrait.setValue(value);
              }
              AssetManager.getType('image').multiSelect = true;
            }
            return this.inputEl;
          },
          getRenderValue: function () {
            if (this.model.get('value') && this.model.get('value').items) {
              return  this.model.get('value').items.map(function(item){
                item.uri = 'public:/' + item.src.replace(location.origin, '');
                return item;
              });
            }
            return  [];
          },
          setInputValue: function (value) {
            galleryTrait.setValue(value);
          },
          setValueFromAssetManager: function (value) {
            for (var x in value) {
              galleryTrait.addImage(value[x]);
            }
          },
          isMultiSelect: true
        })
      );
     });
    }
  };
})(jQuery, Drupal);
