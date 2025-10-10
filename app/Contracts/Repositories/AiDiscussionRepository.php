<?php

namespace App\Contracts\Repositories;

use App\Base\Contracts\Repositories\BaseRepository;
use App\Contracts\Interfaces\AiDiscussionInterface;
use App\Models\AiDiscussion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;

class AiDiscussionRepository extends BaseRepository implements AiDiscussionInterface
{
    public function __construct(AiDiscussion $model)
    {
        $this->model = $model;
    }
    /**
     * @inheritDoc
     */
    public function get(): array|Model|Collection|SupportCollection
    {
        return $this->model->all()
            ->groupBy('user_id')
            ->map(function ($items, $userId) {
                return $items->map(function ($item) {
                    return [
                        'ai_discussion_id' => $item->ai_discussion_id,
                        'user_id' => $item->user_id,
                        'conversation' => $item->conversation,
                        'identified_issue' => $item->identified_issue,
                        'summary' => $item->summary,
                        'created_at' => $item->created_at,
                        'updated_at' => $item->updated_at,
                    ];
                });
            });
    }

    /**
     * @inheritDoc
     */
    public function store(array $data): bool|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|array
    {
        return $this->model->create($data);
    }

    /**
     * @inheritDoc
     */
    public function update(array $data, string $uuid): bool|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|array
    {
        return $this->show($uuid)->update($data);
    }

    /**
     * @inheritDoc
     */
    public function delete(string $uuid): bool|array|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
    {
        return $this->show($uuid)->delete();
    }

    /**
     * @inheritDoc
     */
    public function show(string $uuid): \Illuminate\Database\Eloquent\Model|array|\Illuminate\Database\Eloquent\Collection
    {
        return $this->model->findOrFail($uuid);
    }

    public function getByUserUuid(string $uuid): array|Collection|Model
    {
        return $this->model->where('user_id', $uuid)->get();
    }
}
