<?php

namespace Livewire\Features\SupportLifecycleHooks;

use Illuminate\Support\Stringable;
use Livewire\Component;
use Livewire\Livewire;

class Test extends \Tests\TestCase
{
    /** @test */
    public function can_()
    {
        $this->assertTrue(true);
    }

    /** @test */
    public function cant_call_protected_lifecycle_hooks()
    {
        $this->assertTrue(
            collect([
                'mount',
                'hydrate',
                'hydrateFoo',
                'dehydrate',
                'dehydrateFoo',
                'updating',
                'updatingFoo',
                'updated',
                'updatedFoo',
            ])->every(function ($method) {
                return $this->cannotCallMethod($method);
            })
        );
    }

    protected function cannotCallMethod($method)
    {
        try {
            Livewire::test(ForProtectedLifecycleHooks::class)->call($method);
        } catch (DirectlyCallingLifecycleHooksNotAllowedException $e) {
            return true;
        }

        return false;
    }
    
    /** @test */
    public function boot_method_is_called_on_mount_and_on_subsequent_updates()
    {
        Livewire::test(ComponentWithBootMethod::class)
            ->assertSet('memo', 'bootmountbooted')
            ->call('$refresh')
            ->assertSet('memo', 'boothydratebooted');
    }

    /** @test */
    public function boot_method_can_be_added_to_trait()
    {
        Livewire::test(ComponentWithBootTrait::class)
            ->assertSet('memo', 'boottraitboottraitinitializemountbootedtraitbooted')
            ->call('$refresh')
            ->assertSet('memo', 'boottraitboottraitinitializehydratebootedtraitbooted');
    }

    /** @test */
    public function boot_method_supports_dependency_injection()
    {
        Livewire::test(ComponentWithBootMethodDI::class)
            ->assertSet('memo', 'boottraitbootbootedtraitbooted')
            ->call('$refresh')
            ->assertSet('memo', 'boottraitbootbootedtraitbooted');
    }
}

class ForProtectedLifecycleHooks extends Component
{
    public function mount()
    {
        //
    }

    public function hydrate()
    {
        //
    }

    public function hydrateFoo()
    {
        //
    }

    public function dehydrate()
    {
        //
    }

    public function dehydrateFoo()
    {
        //
    }

    public function updating($name, $value)
    {
        //
    }

    public function updated($name, $value)
    {
        //
    }

    public function updatingFoo($value)
    {
        //
    }

    public function updatedFoo($value)
    {
        //
    }

    public function render()
    {
        return app('view')->make('null-view');
    }
}

class ComponentWithBootMethod extends Component
{
    // Use protected property to record all memo's
    // as hydrating memo wipes out changes from boot
    protected $_memo = '';
    public $memo = '';

    public function boot()
    {
        $this->_memo .= 'boot';
    }

    public function mount()
    {
        $this->_memo .= 'mount';
    }

    public function hydrate()
    {
        $this->_memo .= 'hydrate';
    }

    public function booted()
    {
        $this->_memo .= 'booted';
    }

    public function render()
    {
        $this->memo = $this->_memo;

        return view('null-view');
    }
}

class ComponentWithBootTrait extends Component
{
    use BootMethodTrait;

    // Use protected property to record all memo's
    // as hydrating memo wipes out changes from boot
    protected $_memo = '';
    public $memo = '';

    public function boot()
    {
        $this->_memo .= 'boot';
    }

    public function mount()
    {
        $this->_memo .= 'mount';
    }

    public function hydrate()
    {
        $this->_memo .= 'hydrate';
    }

    public function booted()
    {
        $this->_memo .= 'booted';
    }

    public function render()
    {
        $this->memo = $this->_memo;

        return view('null-view');
    }
}

trait BootMethodTrait
{
    public function bootBootMethodTrait()
    {
        $this->_memo .= 'traitboot';
    }

    public function initializeBootMethodTrait()
    {
        $this->_memo .= 'traitinitialize';
    }

    public function bootedBootMethodTrait()
    {
        $this->_memo .= 'traitbooted';
    }
}

trait BootMethodTraitWithDI
{
    public function bootBootMethodTraitWithDI(Stringable $string)
    {
        $this->_memo .= $string->append('traitboot');
    }

    public function bootedBootMethodTraitWithDI(Stringable $string)
    {
        $this->_memo .= $string->append('traitbooted');
    }
}

class ComponentWithBootMethodDI extends Component
{
    use BootMethodTraitWithDI;

    // Use protected property to record all memo's
    // as hydrating memo wipes out changes from boot
    protected $_memo = '';
    public $memo = '';

    public function boot(Stringable $string)
    {
        $this->_memo .= $string->append('boot');
    }

    public function booted(Stringable $string)
    {
        $this->_memo .= $string->append('booted');
    }

    public function render()
    {
        $this->memo = $this->_memo;

        return view('null-view');
    }
}