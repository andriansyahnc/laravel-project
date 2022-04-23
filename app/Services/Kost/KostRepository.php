<?php

namespace App\Services\Kost;

use App\Models\Kost;
use Illuminate\Http\Request;

class KostRepository
{
    public function store($data, $user_id) 
    {
        $kost = new Kost();
        $kost->name = $data["name"];
        $kost->description = $data["description"];
        $kost->room_area = $data["room_area"];
        $kost->location = $data["location"];
        $kost->price = $data["price"];
        $kost->user_id = $user_id;
        $kost->save();
        return $kost;
    }

    public function findByOwner($user_id)
    {
        return Kost::where('user_id', $user_id)->get();
    }

    public function findById($id, $user_id = NULL)
    {
        return Kost::where('id', $id)->first();
    }

    public function update(Kost $kost, $params)
    {
        foreach($params as $key => $value) {
            $kost->{$key} = $value;
        }
        $kost->save();
        return $kost;
    }

    public function delete(Kost $kost)
    {
        return $kost->delete();
    }

    public function buildSearchParams(Request $request)
    {
        $mapping = [
            'contains' => 'like',
            'gte' => '>=',
            'lte' => '<=',
            'lt' => '<',
            'gt' => '>',
            'between' => 'between',
            'is' => '=',
        ];

        $search_param = $request->input('search');
        if (!$search_param) {
            return [];
        }
        $params = [];
        foreach ($search_param as $idx => $param) {
            $key = array_key_last($param);
            $value = $param[$key];
            $op = $mapping[$key];
            if ($op === 'between') {
                $values = explode(',', $value);
                if (count($values) === 2) {
                    $params[] = [$idx, '>=', (int) $values[0]];
                    $params[] = [$idx, '<=', (int) $values[1]];
                }
                continue;
            }
            if ($op === 'like') {
                $value = "%{$value}%";
            }
            else if ($op !== '=') {
                $value = (int) $value;
            } 
            $params[] = [$idx, $op, $value];
        }

        return $params;
    }

    public function buildSortParams(Request $request)
    {
        $sort = $search_param = $request->input('sort');
        if (!$sort) {
            return [];
        }
        $sort_params = explode(',', $sort);
        $sort_data = [];
        foreach ($sort_params as $sort_param) {
            if (substr($sort_param, 0, 1) === "-") {
                $name = str_replace('-', '', $sort_param);
                $sort_data[] = [$name, 'DESC'];
            } else {
                $sort_data[] = [$sort_param, 'ASC'];
            }
        }
        return $sort_data;
    }

    public function findByParams($params) 
    {
        $search = $params['search'];
        $kost_query = Kost::select('*');
        if (!empty($search)) {
            $kost_query->where($search);
        }
        foreach ($params['sort'] as $sort) {
            $kost_query->orderBy($sort[0], $sort[1]);
        }
        return $kost_query->get();
    }
}
