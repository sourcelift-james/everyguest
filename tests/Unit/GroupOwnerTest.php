<?php

namespace Tests\Unit;

use App\Models\Group;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GroupOwnerTest extends TestCase
{
    use WithFaker;

    private $user;
    private $group;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = factory(User::class)->create();

        $this->group = factory(Group::class)->create([
            'owner_id' => $this->user->id
        ]);

        $this->user->group_id = $this->group->id;
        $this->user->save();
    }

    /** @test */
    public function has_attributes()
    {
        $this->assertIsString($this->user->email);
        $this->assertIsString($this->user->name);
        $this->assertIsString($this->user->password);
    }

    /** @test */
    public function has_group()
    {
        $this->assertIsInt($this->user->group_id);
    }

    /** @test */
    public function is_owner_of_group()
    {
        $this->assertTrue($this->user->id === $this->group->owner_id);
    }
}
