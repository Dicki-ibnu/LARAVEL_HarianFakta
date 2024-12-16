<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CategoryController extends Controller
{
    // Menampilkan daftar category
    public function index()
    {
        $categories = Category::all();
        return view('categories.categories', compact('categories'));
    }

    // Menampilkan form untuk menambah category
    public function create()
    {
        return view('categories.categories-entry');
    }

    // Mengirim data category ke database
    public function store(Request $request)
    {
        // Validasi input data dari user
        $request->validate([
            'nama' => 'required',
            'tanggal' => 'required|date',
            'deskripsi' => 'required',
            'gambar' => 'required|file|mimes:png,jpg,jpeg|max:2048',
        ]);

        try {
            // Mengupload gambar ke folder tujuan
            $gambar = $request->file('gambar');
            $nama_gambar = time() . '_' . $gambar->getClientOriginalName();
            $tujuan_upload = 'uploads/';
            $gambar->move($tujuan_upload, $nama_gambar);

            // Membuat data Category di database
            Category::create([
                'nama' => $request->nama,
                'tanggal' => $request->tanggal,
                'deskripsi' => $request->deskripsi,
                'gambar' => $nama_gambar,
            ]);

            return redirect('/category')->with('success', 'Category berhasil ditambahkan');
        } catch (\Exception $e) {
            return back()->withErrors(['gambar' => 'Gagal mengunggah gambar: ' . $e->getMessage()]);
        }
    }

    // Mengedit data category
    public function edit($id_categories)
    {
        $category = Category::find($id_categories);
        if (!$category) {
            return redirect('/category')->withErrors(['error' => 'Category tidak ditemukan']);
        }

        return view('categories.categories-edit', compact('category'));
    }

    // Memperbarui data category
    public function update(Request $request, $id_categories)
    {
        $request->validate([
            'nama' => 'required',
            'tanggal' => 'required|date',
            'deskripsi' => 'required',
            'gambar' => 'file|mimes:png,jpg,jpeg|max:2048',
        ]);

        $category = Category::find($id_categories);

        if (!$category) {
            return redirect('/category')->withErrors(['error' => 'Category tidak ditemukan']);
        }

        try {
            if ($request->hasFile('gambar')) {
                // Menghapus gambar lama jika ada
                if (file_exists('uploads/' . $category->gambar)) {
                    File::delete('uploads/' . $category->gambar);
                }

                $gambar = $request->file('gambar');
                $nama_gambar = time() . '_' . $gambar->getClientOriginalName();
                $gambar->move('uploads/', $nama_gambar);
                $category->gambar = $nama_gambar;
            }

            // Memperbarui data category di database
            $category->update([
                'nama' => $request->nama,
                'tanggal' => $request->tanggal,
                'deskripsi' => $request->deskripsi,
            ]);

            return redirect('/category')->with('success', 'Category berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->withErrors(['gambar' => 'Gagal memperbarui gambar: ' . $e->getMessage()]);
        }
    }

    // Menghapus category dari database
    public function delete($id_categories)
    {
        $category = Category::find($id_categories);

        if (!$category) {
            return redirect('/category')->withErrors(['error' => 'Category tidak ditemukan']);
        }

        return view('categories.categories-hapus', compact('category'));
    }

    // Menghapus data category secara permanen
    public function destroy($id_categories)
    {
        $category = Category::find($id_categories);

        if (!$category) {
            return redirect('/category')->withErrors(['error' => 'Category tidak ditemukan']);
        }

        try {
            if (file_exists('uploads/' . $category->gambar)) {
                File::delete('uploads/' . $category->gambar);
            }

            $category->delete();
            return redirect('/category')->with('success', 'Category berhasil dihapus');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus category: ' . $e->getMessage()]);
        }
    }
}
