(function ($, Drupal) {
  Drupal.behaviors.pagedesigner_init_responsive_images = {
    attach: function (context, settings) {

      $(document).on('pagedesigner-before-setup', function (e, editor) {
        Twig.extendFunction('json_decode', function (value) {
          return JSON.parse(value);
        });
      });


      // extend image trait
      $(document).on('pagedesigner-init-base-components', function (e, editor) {

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
              this.model.setTargetValue(value, opts);
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
            if (!this.inputEl) {
              var value = this.model.getTargetValue();
              var input = $('<input type="hidden" value="' + value + '" />');
              this.inputEl = input.get(0);
            }
            return this.inputEl;
          },
          calculateSizes: function () {
            let $el = $(this.target.view.el);
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

          afterInit: function () {
            this.model.set('value', JSON.stringify(this.calculateSizes()));
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

            if (!value) {
              return '';
            }

            let templateField = patterns[this.target.attributes.type].additional.responsive_images.template_fields[this.model.get('name')];
            let sizesField = patterns[this.target.attributes.type].additional.responsive_images.component_sizes_field;

            let template = '';
            if (this.target.attributes.attributes[templateField]) {
              template = this.target.attributes.attributes[templateField];
              if (typeof this.target.attributes.attributes[templateField] != "string") {
                template = this.target.attributes.attributes[templateField][0];
              }
            }

            let sizes = {};
            if (this.target.attributes.attributes[sizesField] && typeof this.target.attributes.attributes[sizesField] == 'string' ) {
              sizes = JSON.parse(this.target.attributes.attributes[sizesField]);
            }

            if (template && drupalSettings.pagedesigner_responsive_images.image_style_templates[template]) {

              let output = {
                'img_original': value.src,
                'img_responsive': {}
              }

              Object.keys(drupalSettings.pagedesigner_responsive_images.image_style_templates[template].settings).forEach(function (breakpoint) {
                output['img_responsive'][breakpoint] = {
                  'srcset': '',
                  'size': ''
                }
                Object.keys(drupalSettings.pagedesigner_responsive_images.image_style_templates[template].settings[breakpoint]).forEach(function (imageStyle) {
                  output['img_responsive'][breakpoint]['srcset'] += value.src.replace('/files/', '/files/styles/' + imageStyle + '/public/') + ' ' + drupalSettings.pagedesigner_responsive_images.image_style_templates[template].settings[breakpoint][imageStyle] + ", ";
                  output['img_responsive'][breakpoint]['sizes'] = sizes[breakpoint];
                })
              });

              return JSON.stringify(output);
            }

            return value.src;

          },

        });


































        // editor.PDMediaManager.addTrait('image', 'file', {

        //   afterInit: function () {

        //     // attach template field
        //     this.getTemplateField();

        //     // set sizes
        //     let value = this.model.get('value');
        //     value.sizes = this.calculateSizes();
        //     this.model.set('value', value);

        //     $(this.inputEl).on(
        //       'click',
        //       (function (trait) {
        //         return function (e) {
        //           editor.PDMediaManager.loadForm(trait);
        //         }
        //       }(this))
        //     );
        //   },

        //   getTemplateField: function () {
        //     let options = '<option value="">' + Drupal.t('Choose template') + '</option>';
        //     for (let id of Object.keys(drupalSettings.pagedesigner_responsive_images.image_style_templates)) {
        //       if (this.model.get('value') && this.model.get('value').image_style_template && this.model.get('value').image_style_template == id) {
        //         options += '<option value="' + id + '" selected>' + drupalSettings.pagedesigner_responsive_images.image_style_templates[id] + '</option>';
        //       } else {
        //         options += '<option value="' + id + '">' + drupalSettings.pagedesigner_responsive_images.image_style_templates[id] + '</option>';
        //       }
        //       options += '<option value="' + id + '">' + drupalSettings.pagedesigner_responsive_images.image_style_templates[id] + '</option>';
        //     }
        //     let $select = $('<select>' + options + '</select>');
        //     let self = this;
        //     $select.on('change', function () {
        //       let value = self.model.get('value');
        //       value.image_style_template = $(this).val();
        //       self.model.set('value', value);
        //     });
        //     $('<div class="gjs-field gjs-field-img-style-tmpl">').append($('<div class="gjs-input-holder">').append($select)).insertBefore(this.$metaHolder);
        //   },

        //   calculateSizes: function () {
        //     let $el = $(this.target.view.el);
        //     let $parents = $el.parentsUntil('[data-gjs-type="container"]', Object.keys(drupalSettings.pagedesigner_responsive_images.sizes).join(',')).toArray();
        //     let sizes = {};
        //     $parents.reverse().forEach(function (parent) {
        //       for (let selector of Object.keys(drupalSettings.pagedesigner_responsive_images.sizes)) {
        //         if ($(parent).is(selector)) {
        //           for (let size of Object.keys(drupalSettings.pagedesigner_responsive_images.sizes[selector])) {
        //             if (sizes[size]) {
        //               if (typeof drupalSettings.pagedesigner_responsive_images.sizes[selector][size] == 'number') {
        //                 sizes[size] += ' * ' + drupalSettings.pagedesigner_responsive_images.sizes[selector][size];
        //               } else {
        //                 sizes[size] = ' min( ' + sizes[size] + ' , ' + drupalSettings.pagedesigner_responsive_images.sizes[selector][size] + ' ) ';
        //               }
        //             } else {
        //               sizes[size] = drupalSettings.pagedesigner_responsive_images.sizes[selector][size];
        //             }
        //           }
        //           break
        //         }
        //       }
        //     });

        //     Object.keys(sizes).map(function (key, index) {
        //       sizes[key] = 'calc(' + sizes[key] + ')';
        //     });

        //     return sizes;
        //   },

        //   getMetaData: function getMetaData() {
        //     if (!this.$metaHolder.hasClass('btn-remove')) {
        //       var trait = this;
        //       this.$metaHolder.attr('title', Drupal.t('Remove image'));
        //       this.$metaHolder.click(function () {
        //         if (confirm(Drupal.t('Remove image from component?'))) {
        //           trait.model.set('value', { id: null });
        //           trait.getMetaData()
        //         }
        //       });
        //       this.$metaHolder.addClass('btn-remove');
        //     }
        //     if (this.model.get('value') && this.model.get('value').src) {
        //       return '<img src="' + this.model.get('value').src + '"/>';
        //     }
        //     return '';
        //   }

        // });
      });



      // // Extend component
      // $(document).on('pagedesigner-init-commands', function (e, editor, options) {

      //   editor.on('component:selected', (component, sender) => {
      //     let $el = $(component.view.el);
      //     let $parents = $el.parentsUntil('[data-gjs-type="container"]', Object.keys(drupalSettings.pagedesigner_responsive_images.sizes).join(',')).toArray();
      //     let calc = {};
      //     $parents.reverse().forEach(function (parent) {
      //       for (let selector of Object.keys(drupalSettings.pagedesigner_responsive_images.sizes)) {
      //         if ($(parent).is(selector)) {
      //           for (let size of Object.keys(drupalSettings.pagedesigner_responsive_images.sizes[selector])) {
      //             if (calc[size]) {
      //               if (typeof drupalSettings.pagedesigner_responsive_images.sizes[selector][size] == 'number') {
      //                 calc[size] += ' * ' + drupalSettings.pagedesigner_responsive_images.sizes[selector][size];
      //               } else {
      //                 calc[size] = ' min( ' + calc[size] + ' , ' + drupalSettings.pagedesigner_responsive_images.sizes[selector][size] + ' ) ';
      //               }
      //             } else {
      //               calc[size] = drupalSettings.pagedesigner_responsive_images.sizes[selector][size];
      //             }
      //           }
      //           break
      //         }
      //       }
      //     });
      //     console.log('calc( ' + calc['1200px'] + ' )');
      //     console.log('calc( ' + calc['768px'] + ' )');
      //     console.log('calc( ' + calc['300px'] + ' )');
      //   });
      // });
    }
  };
})(jQuery, Drupal);
