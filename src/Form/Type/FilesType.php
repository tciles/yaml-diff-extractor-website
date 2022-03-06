<?php

declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class FilesType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file_a', FileType::class, [
                'label' => false,
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\File([
                        'maxSize' => '4096k',
                        'mimeTypes' => ['application/x-yaml', 'text/plain']
                    ])
                ]
            ])
            ->add('file_b', FileType::class, [
                'label' => false,
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\File([
                        'maxSize' => '4096k',
                        'mimeTypes' => ['application/x-yaml', 'text/plain'],
                    ])
                ]
            ]);
    }
}