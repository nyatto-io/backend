<?php

namespace App\Http\Controllers;

use App\Models\Anime;
use App\Models\Favorite;
use App\Models\Manga;
use App\Rules\DynamicModelExists;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FavoriteController extends Controller
{
    protected $map = [
        'manga' => Manga::class,
        'anime' => Anime::class,
    ];

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $builder = $request->user()
            ->favorites();
        if ($request->has('type')) {
            $type = $request->validate([
                'type' => ['required', 'string', Rule::in(array_keys($this->map))]
            ])['type'];

            $builder = $builder->{$type}();
        }
        if ($request->has('keyword')) {
            $keyword = $request->validate([
                'keyword' => ['required', 'string'],
            ])['keyword'];

            $constraints = [
                'group',
                'genres',
                'title',
            ];

            if ($request->input('type') === 'anime') {
                $constraints = array_merge($constraints, [
                    'english_title',
                    'synonyms',
                    'japanese_title',
                ]);
            }

            $builder = $builder->whereHas(function (Builder $query) use ($constraints, $keyword) {
                foreach ($constraints as $constraint) {
                    $query->orWhere($constraint, 'LIKE', sprintf('%%%s%%', $keyword));
                }
            });
        }
        return $builder
            ->with('favorable.image')
            ->orderBy('group', 'DESC')
            ->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $data = $request->validate([
            'favorable_type' => ['required', 'string', Rule::in(array_keys($this->map))],
            'favorable_id' => ['required', 'numeric', new DynamicModelExists($this->map, 'favorable_type')],
        ]);

        /**
         * @var \App\Models\User
         */
        $user = $request->user();

        $type = $data['favorable_type'];

        $class = $this->map[$type];

        /**
         * @var \App\Interfaces\Favorable|\App\Models\Model
         */
        $model = $class::findOrFail($data['favorable_id']);

        $favorite = $user->favorites()
            ->{$type}()
            ->where('favorable_id', $model->getKey())
            ->first();

        if ($favorite) {
            $favorite->delete();
            return response('', 204);
        }

        return $user->favorites()->save($model->favorites()->make([
            'group' => $model->group,
            'type' => $type,
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        return $request->user()
            ->favorites()
            ->with('favorable')
            ->findOrFail($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        /**
         * @var Favorite
         */
        $favorite = $request->user()->favorites()->findOrFail($id);

        $favorite->delete();

        return response('', 204);
    }
}
