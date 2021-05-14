<?php

namespace Drupal\pagedesigner_responsive_images_gallery\Plugin\pagedesigner\Handler;

use Drupal\pagedesigner\Entity\Element;
use Drupal\pagedesigner_gallery\Plugin\pagedesigner\Handler\Gallery;
use Symfony\Component\Yaml\Yaml as YamlParser;
use Drupal\Component\Serialization\Yaml as YamlSerializer;

/**
 * Process entities of type "gallery".
 *
 * @PagedesignerHandler(
 *   id = "responsive_image_gallery",
 *   name = @Translation("gallery handler"),
 *   types = {
 *      "gallery",
 *   },
 * )
 */
class ResponsiveImageGallery extends Gallery {

  /**
   * {@inheritdoc}
   */
  public function addResponsiveImages(Element $entity, $data) {

    if ($entity->field_gallery->entity != NULL) {

      // add responsive img URLs
      $patternAdditionalDefinition = $this->getPatternDefinition($entity->parent->entity->field_pattern->value)->getAdditional();
      if (is_array($patternAdditionalDefinition) && array_key_exists('responsive_images', $patternAdditionalDefinition)) {
        $templateField = $patternAdditionalDefinition['responsive_images']['template_fields'][$entity->field_placeholder->value];
        $sizesField = $patternAdditionalDefinition['responsive_images']['component_sizes_field'];

        if ($templateField && $sizesField) {
          $template = FALSE;
          // Hacky, needs rework.
          foreach ($entity->parent->entity->children as $item) {
            if ($item->entity->field_placeholder->value == $templateField) {
              $template = $item->entity->field_content->value;
            }

            if ($item->entity->field_placeholder->value == $sizesField) {
              $sizes = json_decode($item->entity->field_content->value, TRUE);
            }
          }

          if ($template) {
            $config = \Drupal::entityTypeManager()->getStorage('image_style_template')->load($template);
            if ($config) {

              if ($entity->field_gallery->entity->children) {
                foreach ($entity->field_gallery->entity->children as $key => $item) {

                  if ($item->entity != NULL) {
                    $file = $item->entity->field_media->entity->field_media_image->entity;
                    $imgUri = $file->getFileUri();
                    $settings = YamlParser::parse(YamlSerializer::decode($config->settings));

                    array_walk($settings, function (&$imageStyles, $breakpoint) use ($sizes, $imgUri) {
                      $src = '';

                      foreach ($imageStyles as $imageStyle => $width) {
                        $style = \Drupal::entityTypeManager()
                          ->getStorage('image_style')
                          ->load($imageStyle);
                        $src .= $style->buildUrl($imgUri) . ' ' . $width . ', ';
                      }

                      $imageStyles = [
                        'srcset' => substr($src, 0, -2),
                        'sizes' => $sizes[$breakpoint],
                      ];
                    });

                    $data[$key]['img_responsive'] = $settings;
                  }
                }
              }
            }
          }
        }
      }
    }

    return $data;
  }


  /**
   * {@inheritdoc}
   */
  public function render(Element $entity, array &$build = []) {

    if ($entity->field_gallery->entity != NULL) {
      $build = $this->elementHandler->serialize($entity->field_gallery->entity) + $build;
      \Drupal::service('renderer')->addCacheableDependency($build, $entity->field_gallery->entity);
    }
    \Drupal::service('renderer')->addCacheableDependency($build, $entity);

    $this->addResponsiveImages($entity, $build);


//     if ($entity->field_gallery->entity != NULL) {



// $build = $this->serialize($entity);


// print_r($build);

//       \Drupal::service('renderer')->addCacheableDependency($build, $entity->field_gallery->entity);
//     }
//     \Drupal::service('renderer')->addCacheableDependency($build, $entity);
  }




  // /**
  //  * {@inheritdoc}
  //  */
  // public function get(Element $entity, string &$result = '') {
  //   if ($entity->field_gallery->entity != NULL) {


  //     $patternAdditionalDefinition = $this->getPatternDefinition($entity->parent->entity->field_pattern->value)->getAdditional();

  //     if (is_array($patternAdditionalDefinition) && array_key_exists('responsive_images', $patternAdditionalDefinition)) {
  //       $templateField = $patternAdditionalDefinition['responsive_images']['template_fields'][$entity->field_placeholder->value];
  //       $sizesField = $patternAdditionalDefinition['responsive_images']['component_sizes_field'];

  //       if ($templateField && $sizesField) {
  //         $template = FALSE;
  //         // Hacky, needs rework.
  //         foreach ($entity->parent->entity->children as $item) {
  //           if ($item->entity->field_placeholder->value == $templateField) {
  //             $template = $item->entity->field_content->value;
  //           }

  //           if ($item->entity->field_placeholder->value == $sizesField) {
  //             $sizes = json_decode($item->entity->field_content->value, TRUE);
  //           }
  //         }

  //         \Drupal::logger('pagedesigner_responsive_images_gallery')->notice($template);
  //         \Drupal::logger('pagedesigner_responsive_images_gallery')->notice(json_encode($sizes));

  //         if ($template) {
  //           $config = \Drupal::entityTypeManager()->getStorage('image_style_template')->load($template);
  //           if ($config) {

  //             $data = [];
  //             if ($entity->field_gallery->entity->children) {
  //               \Drupal::logger('pagedesigner_responsive_images_gallery')->notice("has children");
  //               foreach ($entity->field_gallery->entity->children as $item) {

  //                 if ($item->entity != NULL) {
  //                   $file = $item->entity->field_media->entity->field_media_image->entity;
  //                   $imgUri = $file->getFileUri();
  //                   \Drupal::logger('pagedesigner_responsive_images_gallery')->notice($imgUri);
  //                   \Drupal::logger('pagedesigner_responsive_images_gallery')->notice(\serialize($settings));

  //                   $settings = YamlParser::parse(YamlSerializer::decode($config->settings));

  //                   array_walk($settings, function (&$imageStyles, $breakpoint) use ($sizes, $imgUri) {
  //                     $src = '';
  //                     foreach ($imageStyles as $imageStyle => $width) {
  //                       $style = \Drupal::entityTypeManager()
  //                         ->getStorage('image_style')
  //                         ->load($imageStyle);
  //                       $src .= $style->buildUrl($imgUri) . ' ' . $width . ', ';
  //                     }
  //                     $imageStyles = [
  //                       'srcset' => substr($src, 0, -2),
  //                       'sizes' => $sizes[$breakpoint],
  //                     ];
  //                   });


  //                   $previewUrl = $file->createFileUrl(FALSE);
  //                   $style = \Drupal::entityTypeManager()
  //                     ->getStorage('image_style')
  //                     ->load('pagedesigner_default');
  //                   if ($style != NULL) {
  //                     $previewUrl = $style->buildUrl($file->getFileUri());
  //                   }
  //                   $style = \Drupal::entityTypeManager()
  //                     ->getStorage('image_style')
  //                     ->load('thumbnail');
  //                   if ($style != NULL) {
  //                     $previewUrl = $style->buildUrl($file->getFileUri());
  //                   }


  //                   $data[] = [
  //                     'alt' => $item->entity->field_content->value,
  //                     'id' => $item->entity->field_media->target_id,
  //                     'img_original' => $file->createFileUrl(FALSE),
  //                     'img_responsive' => $settings,
  //                     'preview' => $previewUrl,
  //                   ];

  //                 }
  //                 // if ($item->entity != NULL) {
  //                 //   if ($item->entity->field_media->entity != NULL && $item->entity->field_media->entity->field_media_image->entity != NULL) {

  //                 //     \Drupal::logger('pagedesigner_responsive_images_gallery')->notice($item->entity->field_media->entity->id());
  //                 //   }
  //                 // }
  //               }
  //             }

  //             // $data = $this->elementHandler->serialize($entity);
  //             // $stringData = [];
  //             // foreach ($data as $item) {
  //             //   $stringData[] = $item['alt'];
  //             // }
  //             // return implode(' ', $stringData);

  //             \Drupal::logger('pagedesigner_responsive_images_gallery')->notice(serialize($data));

  //             // die();
  //             // return;

  //           }
  //         }
  //       }
  //     }





  //     \Drupal::logger('pagedesigner_responsive_images_gallery')->notice("orig: ". $this->elementHandler->get($entity->field_gallery->entity));
  //     $result .= $this->elementHandler->get($entity->field_gallery->entity);



  //     echo $result;

  //     die();




  //   }
  // }

}
