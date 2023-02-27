<?php

namespace Modules\CyberFranco\Http\Controllers\Admin;

use App\Http\Requests\StorePdfRequestRequest;
use App\Jobs\PdfRequestCreate;
use App\Models\PdfRequest;
use App\Models\User;
use App\Services\Cybercheck\CyberCheckApi;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use \Backpack\CRUD\app\Http\Controllers\Operations\BulkDeleteOperation;
use Illuminate\Http\Request;
use Prologue\Alerts\Facades\Alert;
use App\Http\Controllers\Admin\Traits\CreateOperation;
use App\Http\Controllers\Admin\Traits\DeleteOperation;
use App\Http\Controllers\Admin\Traits\UpdateOperation;

/**
 * Class PdfRequestCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class PdfRequestCrudController extends CrudController
{
    use BulkDeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\PdfRequest::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/pdf-request');
        CRUD::setEntityNameStrings('pdf request', 'pdf requests');
        /*PdfRequest::created(function ($entry) {
            if (!$entry->verified) {
                $entry->generateVerification();
            }
        });*/
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->crud->addColumn([
            'name' => 'uuid',
            'label' => 'Uuid',
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'source',
            'label' => 'Source',
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'item',
            'label' => 'Item to analyze',
            'type' => 'text',
        ]);
        $this->crud->addColumn([
            'name' => 'emailuser',
            'label' => 'Email or username',
            'type' => 'text',
        ]);
        CRUD::column('level');
        $this->crud->addColumn([
            'name' => 'status',
            'label' => 'Status',
            'type' => 'text',
            'wrapper' => [
                'element' => 'span',
                'class' => function ($crud, $column, $entry, $related_key) {
                    switch ($column['text']) {
                        case 'created':
                            return 'badge badge-info';
                        case 'in_progress':
                            return 'badge badge-warning';
                        case 'done':
                            return 'badge badge-success';
                        case 'verification_expired':
                        case 'failed':
                        case 'rejected':
                        case 'expired':
                            return 'badge badge-danger';
                        default:
                            return 'badge badge-default';
                    }
                },
            ],
        ]);
        $this->crud->addColumn([
            'name' => 'active',
            'label' => 'Active',
            'type' => 'boolean',
            // 'options' => [0 => 'No', 1 => 'Yes'], // optional
            'wrapper' => [
                'element' => 'span',
                'class' => function ($crud, $column, $entry, $related_key) {
                    if ($column['text'] == 'Yes') {
                        return 'badge badge-success';
                    }

                    return 'badge badge-default';
                },
            ],
        ]);
        CRUD::column('filename');
        $this->crud->addColumn([
            'name' => 'verified',
            'label' => 'Verified',
            'type' => 'boolean',
            // 'options' => [0 => 'No', 1 => 'Yes'], // optional
            'wrapper' => [
                'element' => 'span',
                'class' => function ($crud, $column, $entry, $related_key) {
                    if ($column['text'] == 'Yes') {
                        return 'badge badge-success';
                    }

                    return 'badge badge-default';
                },
            ],
        ]);


        $this->crud->addFilter([
            'name' => 'user',
            'type' => 'select2',
            'label' => 'User'
        ], function () {
            return User::whereNot('id', 1)->get()->keyBy('id')->pluck('name', 'id')->toArray();
        }, function ($value) { // if the filter is active
            $this->crud->addClause('where', 'user_id', $value);
        });

        $this->crud->addFilter(
            [
                'type' => 'text',
                'name' => 'email',
                'label' => 'Email'
            ],
            false,
            function ($value) { // if the filter is active
                $this->crud->addClause('where', 'email', 'LIKE', "%$value%");
            }
        );

        $this->crud->addFilter(
            [
                'type' => 'text',
                'name' => 'uuid',
                'label' => 'Uuid'
            ],
            false,
            function ($value) { // if the filter is active
                $this->crud->addClause('where', 'uuid', 'LIKE', "%$value%");
            }
        );

        $this->crud->addFilter(
            [
                'type' => 'text',
                'name' => 'item',
                'label' => 'Item'
            ],
            false,
            function ($value) { // if the filter is active
                $this->crud->addClause('where', 'item', 'LIKE', "%$value%");
            }
        );

        // dropdown filter
        $this->crud->addFilter([
            'name' => 'source',
            'type' => 'dropdown',
            'label' => 'Source'
        ], PdfRequest::optionArray(), function ($value) { // if the filter is active
            $this->crud->addClause('where', 'source', $value);
        });

        // dropdown filter
        $this->crud->addFilter(
            [
                'type' => 'simple',
                'name' => 'active',
                'label' => 'Active'
            ],
            false,
            function () { // if the filter is active
                $this->crud->addClause('active'); // apply the "active" eloquent scope
            }
        );

        // dropdown filter
        $this->crud->addFilter(
            [
                'type' => 'simple',
                'name' => 'verified',
                'label' => 'Verified'
            ],
            false,
            function () { // if the filter is active
                $this->crud->addClause('verified'); // apply the "active" eloquent scope
            }
        );
//        $this->crud->addButtonFromView('line', 'acurisk', 'acurisk');
//        $this->crud->removeButton('show');

        if (!$this->crud->getRequest()->has('order')) {
            $this->crud->orderBy('uuid');
        }
        $this->crud->setDefaultPageLength(50);

        $this->crud->denyAccess('update');
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(StorePdfRequestRequest::class);
        CRUD::field('item')->label("Item to analyze");
        $this->crud->addField([
            'name' => 'source',
            'type' => 'select_from_array',
            'label' => 'Source',
            'options' => PdfRequest::optionArray(),
            'allows_null' => false,
            'default' => 'internal',
        ]);
        CRUD::field('user_id')->hint("Only if the source is Internal");
        CRUD::field('email')->hint("Only if the source is not Internal");
        CRUD::field('item');
        $this->crud->addField([
            'name' => 'level',
            'type' => 'select_from_array',
            'label' => 'Level',
            'options' => PdfRequest::levelArray(),
            'allows_null' => false,
            'default' => 1,
        ]);


        $this->crud->addField([
            'name' => 'active',
            'type' => 'checkbox',
            'label' => 'Active',
            'default' => true,
            'hint' => 'Available in dashboard',
        ]);
        $this->crud->addField([
            'name' => 'verified',
            'type' => 'checkbox',
            'label' => 'Verified',
            'default' => true,
            'hint' => 'Already Verified item',
        ]);
        $this->crud->addField([
            'name' => 'backpack',
            'type' => 'hidden',
            'value' => '1',
        ]);
        //        $this->crud->addField('profiles');

    }

}
