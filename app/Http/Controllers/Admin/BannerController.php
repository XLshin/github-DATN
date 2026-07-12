<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::latest()->paginate(10);
        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'images'    => 'required|array|min:1',
            'images.*'  => 'image|max:4096',
            'titles'    => 'nullable|array',
            'titles.*'  => 'nullable|string|max:255',
            'link'      => 'nullable|url|max:255',
            'starts_at' => 'nullable|date',
            'ends_at'   => 'nullable|date|after_or_equal:starts_at',
        ]);

        $startsAt = $request->starts_at ? \Carbon\Carbon::parse($request->starts_at) : null;
        $endsAt   = $request->ends_at   ? \Carbon\Carbon::parse($request->ends_at)   : null;

        // Nếu có lịch hẹn và starts_at chưa tới thì để status=false, scheduler sẽ tự bật
        $hasSchedule = $startsAt || $endsAt;
        $autoStatus  = $hasSchedule
            ? ($startsAt === null || $startsAt->lte(now())) // bật ngay nếu starts_at đã qua hoặc không có
            : false; // không hẹn giờ → mặc định ẩn, admin bật tay

        foreach ($request->file('images') as $i => $file) {
            Banner::create([
                'title'     => $request->input("titles.$i") ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                'image'     => $file->store('banners', 'public'),
                'link'      => $request->link,
                'status'    => $autoStatus,
                'starts_at' => $startsAt,
                'ends_at'   => $endsAt,
            ]);
        }

        $count = count($request->file('images'));
        return redirect()->route('admin.banners.index')->with('success', "Thêm $count banner thành công");
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'image'     => 'nullable|image|max:4096',
            'link'      => 'nullable|url|max:255',
            'starts_at' => 'nullable|date',
            'ends_at'   => 'nullable|date|after_or_equal:starts_at',
        ]);

        $data = [
            'title'     => $request->title,
            'link'      => $request->link,
            'status'    => $request->boolean('status'),
            'starts_at' => $request->starts_at ? \Carbon\Carbon::parse($request->starts_at) : null,
            'ends_at'   => $request->ends_at   ? \Carbon\Carbon::parse($request->ends_at)   : null,
        ];

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($banner->image);
            $data['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($data);
        return redirect()->route('admin.banners.index')->with('success', 'Cập nhật banner thành công');
    }

    public function destroy(Banner $banner)
    {
        Storage::disk('public')->delete($banner->image);
        $banner->delete();
        return back()->with('success', 'Xóa banner thành công');
    }

    public function toggleStatus(Banner $banner)
    {
        $banner->update(['status' => !$banner->status]);
        return back()->with('success', 'Cập nhật trạng thái thành công');
    }
}
