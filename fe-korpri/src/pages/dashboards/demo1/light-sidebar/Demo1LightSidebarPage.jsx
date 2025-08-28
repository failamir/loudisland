import { Fragment, useEffect, useState } from 'react';
import { Container } from '@/components/container';
import { Toolbar, ToolbarActions, ToolbarHeading } from '@/layouts/demo1/toolbar';
import { Demo1LightSidebarContent } from './';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Calendar } from '@/components/ui/calendar';
import { addDays, format } from 'date-fns';
import { cn } from '@/lib/utils';
import { KeenIcon } from '@/components/keenicons';
import { Link } from 'react-router-dom';
import axios from 'axios';
const Demo1LightSidebarPage = () => {
  const [date, setDate] = useState({
    from: new Date(2025, 0, 20),
    to: addDays(new Date(2025, 0, 20), 20)
  });
  const [totalIncome, setTotalIncome] = useState(null);
  const [incomeLoading, setIncomeLoading] = useState(false);
  const API_URL = import.meta.env.VITE_APP_API_URL || 'http://localhost:8000/api/v1';

  useEffect(() => {
    let ignore = false;
    setIncomeLoading(true);
    axios
      .get(`${API_URL}/total-income`)
      .then((res) => {
        if (ignore) return;
        const val = res?.data?.total_income ?? 0;
        setTotalIncome(Number(val));
      })
      .finally(() => {
        if (!ignore) setIncomeLoading(false);
      });
    return () => {
      ignore = true;
    };
  }, []);
  return <Fragment>
      <Container>
        <Toolbar>
          <ToolbarHeading title="Dashboard" description="Central Hub for Personal Customization" />
          <ToolbarActions>
            <div className="flex items-center gap-2 me-2">
              <span className="text-gray-600 text-sm">Total Income</span>
              <span className="badge badge-light-success">
                {incomeLoading ? 'Loading…' : new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(totalIncome ?? 0)}
              </span>
            </div>
            <Link to="/withdrawal" className="btn btn-sm btn-primary">
              <KeenIcon icon="wallet" className="me-1" />
              Withdrawal
            </Link>
            <Popover>
              <PopoverTrigger asChild>
                <button id="date" className={cn('btn btn-sm btn-light data-[state=open]:bg-light-active', !date && 'text-gray-400')}>
                  <KeenIcon icon="calendar" className="me-0.5" />
                  {date?.from ? date.to ? <>
                        {format(date.from, 'LLL dd, y')} - {format(date.to, 'LLL dd, y')}
                      </> : format(date.from, 'LLL dd, y') : <span>Pick a date range</span>}
                </button>
              </PopoverTrigger>
              <PopoverContent className="w-auto p-0" align="end">
                <Calendar initialFocus mode="range" defaultMonth={date?.from} selected={date} onSelect={setDate} numberOfMonths={2} />
              </PopoverContent>
            </Popover>
          </ToolbarActions>
        </Toolbar>
      </Container>

      <Container>
        <Demo1LightSidebarContent />
      </Container>
    </Fragment>;
};
export { Demo1LightSidebarPage };