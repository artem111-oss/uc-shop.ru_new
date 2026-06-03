     <?php

     namespace App\Http\Middleware;

     use Closure;
     use Illuminate\Http\Request;

     class RedirectIfFromOtherDomains
     {
         /**
          * Handle an incoming request.
          *
          * @param  \Illuminate\Http\Request  $request
          * @param  \Closure  $next
          * @return mixed
          */
         public function handle(Request $request, Closure $next)
         {
             $mainDomain = 'uc-shop.ru'; // Замените на ваш основной домен
             $allowedDomains = [
                 //'xn--n1ace2a2a.xn--p1ai',
                 'ucshop.pro',
             ];

             $host = $request->getHost();

             if (!in_array($host, $allowedDomains) && $host !== $mainDomain) {
                 return redirect()->to('https://' . $mainDomain . $request->getRequestUri());
             }

             return $next($request);
         }
     }