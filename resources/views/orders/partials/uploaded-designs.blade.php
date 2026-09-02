{{-- ========================================================= --}}
{{-- DESIGN FILES --}}
{{-- ========================================================= --}}

<div class="card mt-4">

    <div class="card-header bg-dark text-white">

        <h5 class="mb-0">

            <i class="fas fa-palette mr-1"></i>

            Designer Files

        </h5>

    </div>


    <div class="card-body">


        @if($order->designFiles->count() > 0)


            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="thead-light">

                        <tr>

                            <th width="80">
                                Version
                            </th>

                            <th>
                                File Name
                            </th>

                            <th width="160">
                                Uploaded By
                            </th>

                            <th width="180">
                                Uploaded At
                            </th>

                            <th>
                                Version Note
                            </th>

                            <th width="180">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        @foreach(
                            $order->designFiles->sortByDesc('version')
                            as $file
                        )


                            <tr>


                                {{-- VERSION --}}

                                <td>

                                    <span class="badge badge-primary">

                                        V{{ $file->version }}

                                    </span>

                                </td>


                                {{-- FILE NAME --}}

                                <td>

                                    <i class="fas fa-file mr-1"></i>

                                    {{ $file->file_name }}

                                </td>


                                {{-- UPLOADER --}}

                                <td>

                                    {{ $file->uploader->name ?? 'Unknown' }}

                                </td>


                                {{-- DATE --}}

                                <td>

                                    {{ $file->created_at->format('d M Y, h:i A') }}

                                </td>


                                {{-- REMARKS --}}

                                <td>

                                    {{ $file->remarks ?? '-' }}

                                </td>


                                {{-- ACTION --}}

                                <td>


                                    {{-- PREVIEW --}}

                                    @if(
                                        in_array(
                                            strtolower($file->file_extension),
                                            ['jpg','jpeg','png','pdf','svg']
                                        )
                                    )

                                        <a
                                            href="{{ route('designs.preview', $file) }}"
                                            target="_blank"
                                            class="btn btn-info btn-sm mb-1">

                                            <i class="fas fa-eye mr-1"></i>

                                            Preview

                                        </a>

                                    @endif


                                    {{-- DOWNLOAD --}}

                                    <a
                                        href="{{ route('designs.download', $file) }}"
                                        class="btn btn-primary btn-sm mb-1">

                                        <i class="fas fa-download mr-1"></i>

                                        Download

                                    </a>


                                </td>


                            </tr>


                        @endforeach


                    </tbody>

                </table>

            </div>


        @else


            <div class="alert alert-secondary mb-0">

                <i class="fas fa-info-circle mr-1"></i>

                No design has been uploaded by the designer yet.

            </div>


        @endif


    </div>

</div>