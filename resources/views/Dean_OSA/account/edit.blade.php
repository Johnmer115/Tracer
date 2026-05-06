@extends('Dean_OSA.layouts.layout')

@section('title', 'Edit Account | SARF Tracking')
@section('page-title', 'Edit Account')

@section('content')
    <section>
        <link rel="stylesheet" href="{{ asset('css/form.css') }}">

        <div class="form-panel">
            <div class="panel-header">
                <div>
                    <div class="panel-title"><i class="fas fa-user-pen"></i> Edit Account</div>
                    <p class="form-copy">Update the account details below. Leave the password blank to keep the current one.</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-body">
                <form action="{{ route('dean_osa.account.update', $account->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div>
                        {{-- User Type --}}
                        <div class="form-group">
                            <label class="form-label" for="usertype">User Type</label>
                            <select class="form-control searchable-select" id="usertype" name="usertype" required>
                                <option value="Dean_OSA"   @selected(old('usertype', $account->usertype) === 'Dean_OSA')>Dean OSA</option>
                                <option value="Staff_OSA"  @selected(old('usertype', $account->usertype) === 'Staff_OSA')>Staff OSA</option>
                                <option value="Branch_OSA" @selected(old('usertype', $account->usertype) === 'Branch_OSA')>Branch OSA</option>
                            </select>
                        </div>

                        {{-- Branch fields — only visible when usertype = Branch_OSA --}}
                        <div id="org-fields" style="{{ old('usertype', $account->usertype) === 'Branch_OSA' ? '' : 'display:none;' }}">
                            <div class="form-group">
                                <label class="form-label" for="branch_id">Branch</label>
                                <select class="form-control searchable-select" id="branch_id" name="branch_id">
                                    <option value="">-- Select Branch --</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            @selected(old('branch_id', $account->branch_id) == $branch->id)>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Username --}}
                        <div class="form-group">
                            <label class="form-label" for="username">Username</label>
                            <input
                                type="text"
                                class="form-control"
                                id="username"
                                name="username"
                                value="{{ old('username', $account->username) }}"
                                placeholder="Enter username"
                                autocomplete="username"
                                required
                            >
                        </div>

                        {{-- Password --}}
                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
                            <div class="password-wrapper">
                                <input
                                    type="password"
                                    class="form-control"
                                    id="password"
                                    name="password"
                                    placeholder="Leave blank to keep current password"
                                    autocomplete="new-password"
                                >
                                <span id="togglePassword" class="toggle-password">
                                    <i class="fas fa-eye"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a class="btn btn-filter" href="{{ route('dean_osa.account.index') }}">Back</a>
                        <button type="submit" class="btn btn-add">Update Account</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {

                const usertypeSelect = document.getElementById('usertype');
                const orgFields      = document.getElementById('org-fields');

                function toggleBranchField() {
                    if (usertypeSelect.value === 'Branch_OSA') {
                        orgFields.style.display = '';
                    } else {
                        orgFields.style.display = 'none';
                    }
                }

                // Run on change
                usertypeSelect.addEventListener('change', toggleBranchField);

                // Run on page load (important for old values)
                toggleBranchField();
            });

            document.getElementById('togglePassword').addEventListener('click', function () {
                const input = document.getElementById('password');
                const type  = input.type === 'password' ? 'text' : 'password';
                input.type  = type;
                this.innerHTML = type === 'password'
                    ? '<i class="fas fa-eye"></i>'
                    : '<i class="fas fa-eye-slash"></i>';
            });
        </script>
    </section>
@endsection