<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Utils;
use App\Http\Requests\MergeItemsRequest;
use App\Models\Item;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function index()
    {

        $items = Item::getByUser(50);

        return $this->ok("Items List", $items);
    }

    public function update(Request $request)
    {
    }

    public function search(Request $request) {

        $result = Item::search($request->query("q"));

        return $this->ok("search result", $result);
    }

    public function merge(MergeItemsRequest $request)
    {

        $uuids = [];

        foreach ($request->item_ids as $id) {

            if (Utils::isValidUuid($id)) {
                $uuids[] = $id;
            }
        }

        Item::updateMultipleTitles($uuids, $request->title);

        $message = sprintf("%s item(s) updated.", count($uuids));
        return $this->ok($message);
    }
}
