import clsx from 'clsx';
import { useFormik } from 'formik';
import { useState } from 'react';
import { Link, useLocation, useNavigate } from 'react-router-dom';
import * as Yup from 'yup';
import { useAuthContext } from '../../useAuthContext';
import { toAbsoluteUrl } from '@/utils';
import { Alert, KeenIcon } from '@/components';
import { useLayout } from '@/providers';
import { faker } from '@faker-js/faker';
const initialValues = {
  name: '',
  email: '',
  password: '',
  uid: faker.string.uuid(),
  nik: '',
  no_hp: '',
  device_name: 'web',
  acceptTerms: false
};
const signupSchema = Yup.object().shape({
  name: Yup.string().min(2, 'Minimum 2 symbols').max(100, 'Maximum 100 symbols'),
  email: Yup.string().email('Wrong email format').min(3, 'Minimum 3 symbols').max(50, 'Maximum 50 symbols').required('Email is required'),
  password: Yup.string().min(6, 'Minimum 6 symbols').max(50, 'Maximum 50 symbols').required('Password is required'),
  uid: Yup.string().required('UID is required'),
  nik: Yup.string().max(50).nullable(),
  no_hp: Yup.string().max(50).nullable(),
  acceptTerms: Yup.bool().required('You must accept the terms and conditions')
});
const Signup = () => {
  const [loading, setLoading] = useState(false);
  const {
    register
  } = useAuthContext();
  const navigate = useNavigate();
  const location = useLocation();
  const from = location.state?.from?.pathname || '/';
  const [showPassword, setShowPassword] = useState(false);
  const {
    currentLayout
  } = useLayout();
  const formik = useFormik({
    initialValues,
    validationSchema: signupSchema,
    onSubmit: async (values, {
      setStatus,
      setSubmitting
    }) => {
      setLoading(true);
      try {
        if (!register) {
          throw new Error('JWTProvider is required for this form.');
        }
        const emailLocal = (values.email || '').split('@')[0] || '';
        const payload = {
          name: values.name || emailLocal,
          email: values.email,
          password: values.password,
          uid: values.uid || faker.string.uuid(),
          nik: values.nik || undefined,
          no_hp: values.no_hp || undefined,
          device_name: values.device_name || 'web'
        };
        await register(payload);
        navigate(from, {
          replace: true
        });
      } catch (error) {
        console.error(error);
        setStatus('The sign up details are incorrect');
        setSubmitting(false);
        setLoading(false);
      }
    }
  });
  const togglePassword = event => {
    event.preventDefault();
    setShowPassword(!showPassword);
  };
  return <div className="card max-w-[370px] w-full">
      <form className="card-body flex flex-col gap-5 p-10" noValidate onSubmit={formik.handleSubmit}>
        <div className="text-center mb-2.5">
          <h3 className="text-lg font-semibold text-gray-900 leading-none mb-2.5">Sign up</h3>
          <div className="flex items-center justify-center font-medium">
            <span className="text-2sm text-gray-600 me-1.5">Already have an Account ?</span>
            <Link to={currentLayout?.name === 'auth-branded' ? '/auth/login' : '/auth/classic/login'} className="text-2sm link">
              Sign In
            </Link>
          </div>
        </div>

        <div className="grid grid-cols-2 gap-2.5">
          <a href="#" className="btn btn-light btn-sm justify-center">
            <img src={toAbsoluteUrl('/media/brand-logos/google.svg')} className="size-3.5 shrink-0" />
            Use Google
          </a>

          <a href="#" className="btn btn-light btn-sm justify-center">
            <img src={toAbsoluteUrl('/media/brand-logos/apple-black.svg')} className="size-3.5 shrink-0 dark:hidden" />
            <img src={toAbsoluteUrl('/media/brand-logos/apple-white.svg')} className="size-3.5 shrink-0 light:hidden" />
            Use Apple
          </a>
        </div>

        <div className="flex items-center gap-2">
          <span className="border-t border-gray-200 w-full"></span>
          <span className="text-2xs text-gray-500 font-medium uppercase">Or</span>
          <span className="border-t border-gray-200 w-full"></span>
        </div>

        {formik.status && <Alert variant="danger">{formik.status}</Alert>}

        <div className="flex flex-col gap-1">
          <label className="form-label text-gray-900">Name</label>
          <label className="input">
            <input placeholder="Your name" type="text" autoComplete="off" {...formik.getFieldProps('name')} className={clsx('form-control bg-transparent', {
            'is-invalid': formik.touched.name && formik.errors.name
          }, {
            'is-valid': formik.touched.name && !formik.errors.name
          })} />
          </label>
          {formik.touched.name && formik.errors.name && <span role="alert" className="text-danger text-xs mt-1">
              {formik.errors.name}
            </span>}
        </div>

        <div className="flex flex-col gap-1">
          <label className="form-label text-gray-900">Email</label>
          <label className="input">
            <input placeholder="email@email.com" type="email" autoComplete="off" {...formik.getFieldProps('email')} className={clsx('form-control bg-transparent', {
            'is-invalid': formik.touched.email && formik.errors.email
          }, {
            'is-valid': formik.touched.email && !formik.errors.email
          })} />
          </label>
          {formik.touched.email && formik.errors.email && <span role="alert" className="text-danger text-xs mt-1">
              {formik.errors.email}
            </span>}
        </div>

        <div className="flex flex-col gap-1">
          <label className="form-label text-gray-900">Password</label>
          <label className="input">
            <input type={showPassword ? 'text' : 'password'} placeholder="Enter Password" autoComplete="off" {...formik.getFieldProps('password')} className={clsx('form-control bg-transparent', {
            'is-invalid': formik.touched.password && formik.errors.password
          }, {
            'is-valid': formik.touched.password && !formik.errors.password
          })} />
            <button className="btn btn-icon" onClick={togglePassword}>
              <KeenIcon icon="eye" className={clsx('text-gray-500', {
              hidden: showPassword
            })} />
              <KeenIcon icon="eye-slash" className={clsx('text-gray-500', {
              hidden: !showPassword
            })} />
            </button>
          </label>
          {formik.touched.password && formik.errors.password && <span role="alert" className="text-danger text-xs mt-1">
              {formik.errors.password}
            </span>}
        </div>

        <div className="flex flex-col gap-1">
          <label className="form-label text-gray-900">UID</label>
          <label className="input">
            <input placeholder="Auto-generated if empty" type="text" autoComplete="off" {...formik.getFieldProps('uid')} className={clsx('form-control bg-transparent', {
            'is-invalid': formik.touched.uid && formik.errors.uid
          }, {
            'is-valid': formik.touched.uid && !formik.errors.uid
          })} />
          </label>
          {formik.touched.uid && formik.errors.uid && <span role="alert" className="text-danger text-xs mt-1">
              {formik.errors.uid}
            </span>}
        </div>

        <div className="flex flex-col gap-1">
          <label className="form-label text-gray-900">NIK (optional)</label>
          <label className="input">
            <input placeholder="NIK" type="text" autoComplete="off" {...formik.getFieldProps('nik')} className={clsx('form-control bg-transparent')} />
          </label>
        </div>

        <div className="flex flex-col gap-1">
          <label className="form-label text-gray-900">No HP (optional)</label>
          <label className="input">
            <input placeholder="08xxxxxxxxxx" type="text" autoComplete="off" {...formik.getFieldProps('no_hp')} className={clsx('form-control bg-transparent')} />
          </label>
        </div>

        <label className="checkbox-group">
          <input className="checkbox checkbox-sm" type="checkbox" {...formik.getFieldProps('acceptTerms')} />
          <span className="checkbox-label">
            I accept{' '}
            <Link to="#" className="text-2sm link">
              Terms & Conditions
            </Link>
          </span>
        </label>

        {formik.touched.acceptTerms && formik.errors.acceptTerms && <span role="alert" className="text-danger text-xs mt-1">
            {formik.errors.acceptTerms}
          </span>}

        <button type="submit" className="btn btn-primary flex justify-center grow" disabled={loading || formik.isSubmitting}>
          {loading ? 'Please wait...' : 'Sign UP'}
        </button>
      </form>
    </div>;
};
export { Signup };