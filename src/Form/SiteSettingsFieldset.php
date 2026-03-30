<?php declare(strict_types=1);

namespace SearchHistory\Form;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;

class SiteSettingsFieldset extends Fieldset
{
    /**
     * @var string
     */
    protected $label = 'Search History'; // @translate

    protected $elementGroups = [
        'search_history' => 'Search History', // @translate
    ];

    public function init(): void
    {
        $this
            ->setAttribute('id', 'search-history')
            ->setOption('element_groups', $this->elementGroups)

            ->add([
                'name' => 'searchhistory_remove_in_account',
                'type' => Element\Checkbox::class,
                'options' => [
                    'element_group' => 'search_history',
                    'label' => 'Hide delete button on search page', // @translate
                    'info' => 'When checked, replace the delete button by a link to the search history page in the user account.', // @translate
                ],
                'attributes' => [
                    'id' => 'searchhistory_remove_in_account',
                ],
            ])
        ;
    }
}
