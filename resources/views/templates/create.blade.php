{{-- resources/views/templates/create.blade.php --}}

@extends('layouts.app') {{-- Make sure to extend from your main layout --}}

@section('content')
    <div class="container">
        <div class="col-md-11">
            <h1>Create New Template</h1>
            <form action="{{ route('templates.store') }}" method="POST">
                @csrf {{-- CSRF token for protection --}}

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="name">Template Name:</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>

                <div class="form-group col-md-2">
                    <label> Header Color:</label>
                    <input type="color" class="form-control" name="header_color" value="#ffffff">
                </div>

                <div class="col-md-12">

                    <div class="form-group">
                        <label for="primary_header">Primary Header:</label>
                        <textarea class="form-control" id="primary_header" name="primary_header"></textarea>
                    </div>

                </div>
                {{-- <div class="col-md-3">
                    <div class="form-group">
                        <label for="secondary_header">Secondary Header:</label>
                        <input type="text" class="form-control" id="secondary_header" name="secondary_header">
                    </div>
                </div> --}}
                <div class="col-md-12">
                    <hr>

                    <h2>Sections</h2>
                    <div id="sections-container" class="col-md-12">
                        {{-- Initial section group will be added dynamically --}}





                    </div>
                    <hr>
                </div>
                <div class="col-md-12">

                    <div class="form-group">
                        <label for="primary_footer">Primary Footer:</label>
                        <textarea class="form-control" id="primary_footer" name="primary_footer"></textarea>
                    </div>

                </div>
                <div class="col-md-12">
                    <button type="button" id="add-section-btn" class="btn btn-primary">Add Two-Column Section</button>
                    <button type="button" id="add-single-section-btn" class="btn btn-primary">Add Single-Column
                        Section</button>
                    <button type="submit" class="btn btn-success">Save Template</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let sectionIndex = 0;

            function initTinyMCE(selector) {
                tinymce.init({
                    selector: selector,
                    // Additional TinyMCE configuration options
                });
            }
            initTinyMCE('#primary_header, #primary_footer');

            $('#add-section-btn').click(function() {
                addTwoColumnSection();
            });

            $('#add-single-section-btn').click(function() {
                addSingleColumnSection();
            });
            $('#sections-container').on('click', '.delete-section-btn', function() {
                $(this).closest('.section_block').remove();
            });

            function addTwoColumnSection() {
                const container = document.getElementById('sections-container');
                const sectionHeaderIdLeft = `header_left_${sectionIndex}`;
                const sectionHeaderIdRight = `header_right_${sectionIndex}`;
                const sectionIdLeft = `content_left_${sectionIndex}`;
                const sectionIdRight = `content_right_${sectionIndex}`;

                const newSectionHTML = `
               

                        <div class="col-md-12 section_block" style="margin-bottom: 20px;">
                            <div class="form-group col-md-2">
                                <label>Section Header Color:</label>
                                <input type="color" class="form-control" name="sections[${sectionIndex}][header_color]" value="#ffffff">
                            </div>
                            <div class="form-group col-md-2">
                                <label>Delete Section</label>
                                <button type="button" class="btn btn-danger delete-section-btn">Delete</button>
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Section Header Right:</label>
                                    <textarea class="form-control" id="${sectionHeaderIdRight}" name="sections[${sectionIndex}][header_right]" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Section Header Left:</label>
                                    <textarea class="form-control" id="${sectionHeaderIdLeft}" name="sections[${sectionIndex}][header_left]" rows="2"></textarea>
                                </div>
                            </div>
                         
                    
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Content Right (Arabic):</label>
                                    <textarea class="form-control" id="${sectionIdRight}" name="sections[${sectionIndex}][content_right]" rows="6"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Content Left (English):</label>
                                    <textarea class="form-control" id="${sectionIdLeft}" name="sections[${sectionIndex}][content_left]" rows="6"></textarea>
                                </div>
                            </div>
                       
                            <input type="hidden" name="sections[${sectionIndex}][order]" value="${sectionIndex + 1}">
                        </div>
                `;

                container.insertAdjacentHTML('beforeend', newSectionHTML);

                // Initialize TinyMCE on the new textarea elements
                initTinyMCE(`#${sectionHeaderIdLeft}`, 'ltr');
                initTinyMCE(`#${sectionHeaderIdRight}`, 'rtl');
                initTinyMCE(`#${sectionIdLeft}`, 'ltr');
                initTinyMCE(`#${sectionIdRight}`, 'rtl');

                sectionIndex++;
            }

            function addSingleColumnSection() {
                const container = document.getElementById('sections-container');
                const sectionId = `content_single_${sectionIndex}`;
                const sectionHeaderIdLeft = `header_left_${sectionIndex}`;
                const sectionHeaderIdRight = `header_right_${sectionIndex}`;

                const newSectionHTML = `
                        <div class="col-md-12 section_block">
                            <div class="form-group col-md-2">
                                <label>Section Header Color:</label>
                                <input type="color" class="form-control" name="sections[${sectionIndex}][header_color]"
                                    value="#ffffff">
                            </div>
                            <div class="form-group col-md-2">
                                <label>Delete Section</label>
                                <button type="button" class="btn btn-danger delete-section-btn">Delete</button>
                            </div>
                            <div class="clearfix"></div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Section Header Right:</label>
                                    <textarea class="form-control" id="${sectionHeaderIdRight}" name="sections[${sectionIndex}][header_right]" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Section Header Left:</label>
                                    <textarea class="form-control" id="${sectionHeaderIdLeft}" name="sections[${sectionIndex}][header_left]" rows="2"></textarea>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Content:</label>
                                    <textarea class="form-control" id="${sectionId}" name="sections[${sectionIndex}][content]" rows="6"></textarea>
                                </div>
                            </div>
                            <input type="hidden" name="sections[${sectionIndex}][order]" value="${sectionIndex + 1}">
                        </div>       
                `;

                container.insertAdjacentHTML('beforeend', newSectionHTML);

                // Initialize TinyMCE on the new textarea element
                initTinyMCE(`#${sectionHeaderIdLeft}`, 'ltr');
                initTinyMCE(`#${sectionHeaderIdRight}`, 'rtl');
                initTinyMCE(`#${sectionId}`);
                sectionIndex++;
            }
        });
    </script>
@endsection
