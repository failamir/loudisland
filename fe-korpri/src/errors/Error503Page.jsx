import useBodyClasses from '@/hooks/useBodyClasses';
import { toAbsoluteUrl } from '@/utils';
import { Link } from 'react-router-dom';
import { Fragment } from 'react/jsx-runtime';

const Error503Page = () => {
  useBodyClasses('dark:bg-coal-500');
  return (
    <Fragment>
      <div className="mb-10">
        <img src={toAbsoluteUrl('/media/illustrations/25.svg')} className="dark:hidden max-h-[160px]" alt="image" />
        <img src={toAbsoluteUrl('/media/illustrations/25-dark.svg')} className="light:hidden max-h-[160px]" alt="image" />
      </div>

      <span className="badge badge-primary badge-outline mb-3">503 Error</span>

      <h3 className="text-2.5xl font-semibold text-gray-900 text-center mb-2">
        Sistem sedang maintenance
      </h3>

      <div className="text-md text-center text-gray-700 mb-10">
        Mohon maaf, sistem sementara tidak dapat diakses. Silakan coba lagi nanti.
      </div>

      <Link to="/auth/login" className="btn btn-primary flex justify-center">
        Kembali ke Login
      </Link>
    </Fragment>
  );
};

export { Error503Page };
